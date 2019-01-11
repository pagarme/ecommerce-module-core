<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

final class ChargeHandlerService extends AbstractHandlerService
{
    /**
     * @param Webhook $webhook
     * @return array
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidOperationException
     */
    protected function handlePaid(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        /**
         *
         * @var Order $order
        */
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
            $outdatedCharge->addTransaction($charge->getLastTransaction());
            $charge = $outdatedCharge;
        }

        $paidAmount = $transaction->getAmount();
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
        $orderService->syncPlatformWith($order);

        $returnMessage = $this->prepareReturnMessage($charge);
        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

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
        $orderService->syncPlatformWith($order);

        $returnMessage = $this->prepareReturnMessage($charge);
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

    //@todo handlePaymentFailed
    protected function handlePaymentFailed_TODO(Webhook $webhook)
    {
        //@todo
        //In simulator, Occurs with values between 1.051,72 and 1.262,06, auth
        // only and auth and capture.
        //AcquirerMessage = Simulator|Transação de simulação negada por falta de crédito, utilizado para realizar simulação de autorização parcial
        //ocurrs in the next case of the simulator too.

        //When this webhook is received, the order wasn't created on magento, so
        // no further action is needed.
    }

    //@todo handleOverpaid
    protected function handleOverpaid_TODO(Webhook $webhook)
    {
        //@todo What should we do when receive a overpaid charge webhook?
        //add history about the extra value.
        //call $this->handlePaid($webook);
        //add info about extra value on return message;
        //return message
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

    protected function handleRefunded(Webhook $webhook)
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
        $orderService->syncPlatformWith($order);

        $returnMessage = $this->prepareReturnMessage($charge);
        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

    /**
     * @param Webhook $webhook
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    protected function loadOrder(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();
        /**
         *
 * @var Charge $charge 
*/
        $charge = $webhook->getEntity();
        $order = $orderRepository->findByMundipaggId($charge->getOrderId());

        if ($order === null) {
            $orderDecoratorClass =
                MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

            /**
             *
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

    private function prepareHistoryComment(Charge $charge)
    {
        $i18n = new LocalizationService();
        $moneyService = new MoneyService();

        if ($charge->getStatus()->equals(ChargeStatus::paid())) {
            $amountInCurrency = $moneyService->centsToFloat($charge->getPaidAmount());

            $history = $i18n->getDashboard(
                'Payment received: %.2f',
                $amountInCurrency
            );

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

    private function prepareReturnMessage(Charge $charge)
    {
        $moneyService = new MoneyService();

        if ($charge->getStatus()->equals(ChargeStatus::paid())) {
            $amountInCurrency = $moneyService->centsToFloat($charge->getPaidAmount());

            $returnMessage = "Amount Paid: $amountInCurrency";

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

        $amountInCurrency = $moneyService->centsToFloat($charge->getRefundedAmount());
        $returnMessage = "Charge canceled. Refunded amount: $amountInCurrency";

        return $returnMessage;
    }
}