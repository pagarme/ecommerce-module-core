<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\InvalidOperationException;
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
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Mundipagg\Core\Kernel\Services\ChargeService;

final class ChargeOrderService extends AbstractHandlerService
{
    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidOperationException
     * @throws InvalidParamException
     */
    protected function handlePaid(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        /**
         * @var Order $order
         */
        $order = $this->order;

        if ($order->getStatus()->equals(OrderStatus::canceled())) {
            $result = [
                "message" => "It is not possible to pay an order that was already canceled.",
                "code" => 200
            ];

            return $result;
        }

        /**
         *
         * @var Charge|ChargeInterface $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();
        /**
         *
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $chargeRepository->findByMundipaggId(
            $charge->getMundipaggId()
        );

        $platformOrder = $this->order->getPlatformOrder();
        if ($outdatedCharge !== null) {
            $outdatedCharge->addTransaction($charge->getLastTransaction());
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

        $orderRepository->save($order);
        $history = $this->prepareHistoryComment($charge);
        $this->order->getPlatformOrder()->addHistoryComment($history);

        $orderService->syncPlatformWith($order, false);

        $this->addWebHookReceivedHistory($webhook);
        $platformOrder->save();

        $returnMessage = $this->prepareReturnMessage($charge);

        $response = $this->tryCancelMultiMethodsWithOrder();

        $result = [
            "message" => $returnMessage . '  ' . $response,
            "code" => 200
        ];

        return $result;
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handlePartialCanceled(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        $order = $this->order;

        /**
         *
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();
        /**
         *
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $chargeRepository->findByMundipaggId(
            $charge->getMundipaggId()
        );
        if ($outdatedCharge !== null) {
            $outdatedCharge->addTransaction($transaction);
            $charge = $outdatedCharge;
        }

        $charge->cancel($transaction->getAmount());

        $order->updateCharge($charge);

        $orderRepository->save($order);
        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history);
        $orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

    protected function handleOverpaid(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    protected function handleUnderpaid(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    protected function handleRefunded(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        $order = $this->order;

        if ($order->getStatus()->equals(OrderStatus::canceled())) {
            $result = [
                "message" => "It is not possible to refund a charge of an order that was canceled.",
                "code" => 200
            ];

            return $result;
        }

        /**
         *
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();
        /**
         *
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $chargeRepository->findByMundipaggId(
            $charge->getMundipaggId()
        );

        if ($outdatedCharge !== null) {
            $charge = $outdatedCharge;
        }

        $cancelAmount = $charge->getAmount();
        if ($transaction !== null) {
            $outdatedCharge->addTransaction($transaction);
            $cancelAmount = $transaction->getAmount();
        }

        $charge->cancel($cancelAmount);

        $order->updateCharge($charge);

        $orderRepository->save($order);
        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history);
        $orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        $this->order = $order;

        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

    //@todo handleProcessing
    protected function handleProcessing_TODO(Webhook $webhook)
    {
        //@todo
        //In simulator, Occurs with values between 1.050,01 and 1.051,71, auth
        // only and auth and capture.
        //AcquirerMessage = Simulator|Ocorreu um timeout (transação simulada)
    }

    protected function handleAntifraudReproved(Webhook $webhook)
    {
        return $this->handlePaymentFailed($webhook);
    }

    protected function handlePaymentFailed(Webhook $webhook)
    {
        $order = $this->order;

        $orderRepository = new OrderRepository();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        /**
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        $outdatedCharge = $chargeRepository->findByMundipaggId(
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

        $orderRepository->save($order);
        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history, false);
        $orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        $response = $this->tryCancelMultiMethodsWithOrder();

        $result = [
            "message" => $returnMessage . '  ' . $response,
            "code" => 200
        ];

        return $result;
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

    //@todo handleCreated
    protected function handleCreated_TODO(Webhook $webhook)
    {
        //@todo, but not with priority,
    }

    //@todo handlePending
    protected function handlePending_TODO(Webhook $webhook)
    {
        //@todo, but not with priority,
    }

    protected function loadOrderByCode(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();

        /* @var Charge $charge */
        $charge = $webhook->getEntity();
        $order = $orderRepository->findByCode($charge->getCode());

        if ($order === null) {
            $orderDecoratorClass =
                MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

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

        $this->order = $order;
    }

    /**
     * @param Webhook $webhook
     * @throws InvalidParamException
     * @throws NotFoundException
     * @throws WebhookHandlerNotFoundException
     */
    protected function loadOrder(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();

        /** @var Charge $charge */
        $charge = $webhook->getEntity();

        $order = $orderRepository->findByMundipaggId($charge->getOrderId());
        if ($order === null) {
            $orderDecoratorClass = MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

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

        $this->order = $order;
    }

    public function prepareHistoryComment(ChargeInterface $charge)
    {
        $i18n = new LocalizationService();
        $moneyService = new MoneyService();

        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $moneyService->centsToFloat($charge->getPaidAmount());

            $history = $i18n->getDashboard(
                'Payment received: %.2f',
                $amountInCurrency
            );

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $history .= ". " . $i18n->getDashboard(
                        "Extra amount paid: %.2f",
                        $moneyService->centsToFloat($extraValue)
                    );
            }

            if ($extraValue < 0) {
                $history .= ". " . $i18n->getDashboard(
                        "Remaining amount: %.2f",
                        $moneyService->centsToFloat(abs($extraValue))
                    );
            }

            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $history = $i18n->getDashboard(
                    'Refunded amount: %.2f',
                    $moneyService->centsToFloat($refundedAmount)
                );
                $history .= " (" . $i18n->getDashboard('until now') . ")";
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $moneyService->centsToFloat($canceledAmount);

                $history .= " ({$i18n->getDashboard('Partial Payment')}";
                $history .= ". " .
                    $i18n->getDashboard(
                        'Canceled amount: %.2f',
                        $amountCanceledInCurrency
                    ) . ')';
            }

            return $history;
        }

        if ($charge->getStatus()->equals(ChargeStatus::failed())) {
            $history = $i18n->getDashboard('Charge failed.');

            return $history;
        }

        $amountInCurrency = $moneyService->centsToFloat($charge->getRefundedAmount());
        $history = $i18n->getDashboard(
            'Charge canceled.'
        );

        $history .= ' ' . $i18n->getDashboard(
                'Refunded amount: %.2f',
                $amountInCurrency
            );
        $history .= " (" . $i18n->getDashboard('until now') . ")";

        return $history;
    }

    public function prepareReturnMessage(ChargeInterface $charge)
    {
        $moneyService = new MoneyService();

        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $moneyService->centsToFloat($charge->getPaidAmount());

            $returnMessage = "Amount Paid: $amountInCurrency";

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $returnMessage .= ". Extra value paid: " .
                    $moneyService->centsToFloat($extraValue);
            }

            if ($extraValue < 0) {
                $returnMessage .= ". Remaining Amount: " .
                    $moneyService->centsToFloat(abs($extraValue));
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $moneyService->centsToFloat($canceledAmount);

                $returnMessage .= ". Amount Canceled: $amountCanceledInCurrency";
            }

            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $returnMessage = "Refunded amount unil now: " .
                    $moneyService->centsToFloat($refundedAmount);
            }

            return $returnMessage;
        }

        if ($charge->getStatus()->equals(ChargeStatus::failed())) {
            return "Charge failed at Mundipagg";
        }

        $amountInCurrency = $moneyService->centsToFloat($charge->getRefundedAmount());
        $returnMessage = "Charge canceled. Refunded amount: $amountInCurrency";

        return $returnMessage;
    }
}
