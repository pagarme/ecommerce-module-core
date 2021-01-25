<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\ChargeInterface;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Payment\Services\ResponseHandlers\OrderHandler;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Kernel\Services\ChargeService;

final class ChargeOrderService extends AbstractHandlerService
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ChargeRepository
     */
    private $chargeRepository;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var OrderHandler
     */
    private $orderHandlerService;

    /**
     * @var MoneyService
     */
    private $moneyService;

    /**
     * @var LocalizationService
     */
    private $i18n;

    /**
     * ChargeOrderService constructor.
     */
    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->chargeRepository = new ChargeRepository();
        $this->orderService = new OrderService();
        $this->orderHandlerService = new OrderHandler();
        $this->moneyService = new MoneyService();
        $this->i18n = new LocalizationService();
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handlePaid(Webhook $webhook)
    {
        /**
         * @var Order $order
         */
        $order = $this->order;

        if ($order->getStatus()->equals(OrderStatus::canceled())) {
            return [
                "message" => "It is not possible to pay an order that was already canceled.",
                "code" => 200
            ];
        }

        /**
         * @var Charge|ChargeInterface $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        /**
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $this->chargeRepository->findByMundipaggId(
            $charge->getMundipaggId()
        );

        $platformOrder = $this->order->getPlatformOrder();
        if ($outdatedCharge !== null) {
            $outdatedCharge->addTransaction($charge->getLastTransaction());
            $outdatedCharge->setStatus($charge->getStatus());
            $charge = $outdatedCharge;
        }

        $paidAmount = $transaction->getPaidAmount();
        if (!$charge->getStatus()->equals(ChargeStatus::paid())) {
            $charge->pay($paidAmount);
        }

        if ($charge->getPaidAmount() == 0) {
            $charge->setPaidAmount($paidAmount);
        }

        $order->updateCharge($charge);
        $this->orderRepository->save($order);

        $history = $this->prepareHistoryComment($charge);
        $this->order->getPlatformOrder()->addHistoryComment($history, false);

        $this->orderService->syncPlatformWith($order, false);
        $this->addWebHookReceivedHistory($webhook);

        $platformOrder->save();

        $response = $this->tryCancelMultiMethodsWithOrder();

        $returnMessage = $this->prepareReturnMessage($charge);

        $order->applyOrderStatusFromCharges();

        $orderHandler = $this->orderHandlerService->handle($order);

        return [
            "code" => 200,
            "message" =>
                $returnMessage . '  ' .
                $response . '  ' .
                $this->treatOrderMessage($orderHandler)
        ];
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handlePartialCanceled(Webhook $webhook)
    {
        $order = $this->order;

        /**
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        /**
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $this->chargeRepository->findByMundipaggId(
            $charge->getMundipaggId()
        );

        if ($outdatedCharge !== null) {
            $outdatedCharge->addTransaction($transaction);
            $outdatedCharge->setStatus($charge->getStatus());
            $charge = $outdatedCharge;
        }

        $charge->cancel($transaction->getAmount());
        $order->updateCharge($charge);
        $this->orderRepository->save($order);

        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history, false);

        $this->orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        $order->applyOrderStatusFromCharges();

        $orderHandler = $this->orderHandlerService->handle($order);

        return [
            "code" => 200,
            "message" =>
                $returnMessage . ' ' .
                $this->treatOrderMessage($orderHandler)
        ];
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleOverpaid(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleUnderpaid(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleRefunded(Webhook $webhook)
    {
        $order = $this->order;

        if ($order->getStatus()->equals(OrderStatus::canceled())) {
            return [
                "message" => "It is not possible to refund a charge of an order that was canceled.",
                "code" => 200
            ];
        }

        /**
         *
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        /**
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $this->chargeRepository->findByMundipaggId(
            $charge->getMundipaggId()
        );

        if ($outdatedCharge !== null) {
            $charge = $outdatedCharge;
        }

        $cancelAmount = $charge->getAmount();
        if ($transaction !== null) {
            $outdatedCharge->addTransaction($transaction);
            $outdatedCharge->setStatus($charge->getStatus());
            $cancelAmount = $transaction->getAmount();
        }

        $charge->cancel($cancelAmount);
        $order->updateCharge($charge);
        $this->orderRepository->save($order);

        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history, false);

        $this->orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        $order->applyOrderStatusFromCharges();

        $orderHandler = $this->orderHandlerService->handle($order);

        return [
            "code" => 200,
            "message" =>
                $returnMessage . ' ' .
                $this->treatOrderMessage($orderHandler)
        ];
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleAntifraudReproved(Webhook $webhook)
    {
        return $this->handlePaymentFailed($webhook);
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handlePaymentFailed(Webhook $webhook)
    {
        $order = $this->order;

        /**
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        $outdatedCharge = $this->chargeRepository->findByMundipaggId(
            $charge->getMundipaggId()
        );

        if ($outdatedCharge !== null) {
            $charge = $outdatedCharge;
        }

        if ($transaction !== null) {
            $outdatedCharge->addTransaction($transaction);
        }

        $charge->failed();
        $order->updateCharge($charge);
        $this->orderRepository->save($order);

        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history, false);

        $this->orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        $response = $this->tryCancelMultiMethodsWithOrder();

        $order->applyOrderStatusFromCharges();

        $orderHandler = $this->orderHandlerService->handle($order);

        return [
            "code" => 200,
            "message" =>
                $returnMessage . '  ' .
                $response . '  ' .
                $this->treatOrderMessage($orderHandler)
        ];
    }

    /**
     * @return string
     */
    private function tryCancelMultiMethodsWithOrder()
    {
        $chargeService = new ChargeService();
        $chargeListPaid = $chargeService->getNotFailedOrCanceledCharges(
            $this->order->getCharges()
        );

        $logService = new LogService(
            'ChargeOrderService',
            true
        );

        $response = [];
        if (!empty($chargeListPaid && count($this->order->getCharges()) > 1)) {
            $logService->info('Try Cancel Charge(s)');

            foreach ($chargeListPaid as $chargePaid) {
                $message =
                    ($chargeService->cancel($chargePaid))->getMessage()
                    . ' - ' .
                    $chargePaid->getMundipaggId()->getValue();

                $logService->info($message);

                $response[] = $message;
            }
        }

        return implode('/', $response);
    }

    /**
     * @param Webhook $webhook
     * @throws InvalidParamException
     * @throws NotFoundException
     */
    protected function loadOrder(Webhook $webhook)
    {
        $this->orderRepository = new OrderRepository();

        /** @var Charge $charge */
        $charge = $webhook->getEntity();

        $order = $this->orderRepository->findByMundipaggId($charge->getOrderId());
        if ($order === null) {
            $orderDecoratorClass = MPSetup::get(
                MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS
            );

            /**
             * @var PlatformOrderInterface $order
             */
            $order = new $orderDecoratorClass();
            $order->loadByIncrementId($charge->getCode());

            $orderFactory = new OrderFactory();
            $order = $orderFactory->createFromPlatformData(
                $order,
                $charge->getOrderId()->getValue()
            );
        }

        $order->setCustomer($webhook->getEntity()->getCustomer());

        $this->order = $order;
    }

    public function prepareHistoryComment(ChargeInterface $charge)
    {
        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $this->moneyService->centsToFloat($charge->getPaidAmount());

            $history = $this->i18n->getDashboard(
                'Payment received: %.2f',
                $amountInCurrency
            );

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $history .= ". " . $this->i18n->getDashboard(
                    "Extra amount paid: %.2f",
                    $this->moneyService->centsToFloat($extraValue)
                );
            }

            if ($extraValue < 0) {
                $history .= ". " . $this->i18n->getDashboard(
                    "Remaining amount: %.2f",
                    $this->moneyService->centsToFloat(abs($extraValue))
                );
            }

            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $history = $this->i18n->getDashboard(
                    'Refunded amount: %.2f',
                    $this->moneyService->centsToFloat($refundedAmount)
                );
                $history .= " (" . $this->i18n->getDashboard('until now') . ")";
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $this->moneyService->centsToFloat($canceledAmount);

                $history .= " ({$this->i18n->getDashboard('Partial Payment')}";
                $history .= ". " .
                    $this->i18n->getDashboard(
                        'Canceled amount: %.2f',
                        $amountCanceledInCurrency
                    ) . ')';
            }

            return $history;
        }

        if ($charge->getStatus()->equals(ChargeStatus::failed())) {
            return $this->i18n->getDashboard('Charge failed.');
        }

        $amountInCurrency = $this->moneyService->centsToFloat($charge->getRefundedAmount());
        $history = $this->i18n->getDashboard(
            'Charge canceled.'
        );

        $history .= ' ' . $this->i18n->getDashboard('Refunded amount: %.2f', $amountInCurrency);
        $history .= " (" . $this->i18n->getDashboard('until now') . ")";

        return $history;
    }

    /**
     * @param ChargeInterface $charge
     * @return string
     * @throws InvalidParamException
     */
    public function prepareReturnMessage(ChargeInterface $charge)
    {
        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $this->moneyService->centsToFloat($charge->getPaidAmount());

            $returnMessage = "Amount Paid: {$amountInCurrency}";

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $returnMessage .= ". Extra value paid: " .
                    $this->moneyService->centsToFloat($extraValue);
            }

            if ($extraValue < 0) {
                $returnMessage .= ". Remaining Amount: " .
                    $this->moneyService->centsToFloat(abs($extraValue));
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $this->moneyService->centsToFloat($canceledAmount);

                $returnMessage .= ". Amount Canceled: {$amountCanceledInCurrency}";
            }

            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $returnMessage = "Refunded amount unil now: " .
                    $this->moneyService->centsToFloat($refundedAmount);
            }

            return $returnMessage;
        }

        if ($charge->getStatus()->equals(ChargeStatus::failed())) {
            return "Charge failed at Mundipagg";
        }

        $amountInCurrency = $this->moneyService->centsToFloat($charge->getRefundedAmount());

        return "Charge canceled. Refunded amount: {$amountInCurrency}";
    }

    /**
     * @param $orderHandler
     * @return string
     */
    private function treatOrderMessage($orderHandler)
    {
        if ($orderHandler) {
            return "";
        }

        return $orderHandler;
    }
}
