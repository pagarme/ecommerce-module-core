<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\InvoiceService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\InvoiceState;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;
use Mundipagg\Core\Payment\Services\ResponseHandlers\OrderHandler;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Webhook\Exceptions\UnprocessableWebhookException;

final class OrderHandlerService extends AbstractHandlerService
{
    protected function handlePaid(Webhook $webhook)
    {
        /* @fixme
         *      On authAndCapture, returns  "Can't create Invoice for the order!
         *      Reason: No items to be invoiced or M2 Action Flag Invoice is false"         *
         */

        $order = $this->order;
        $result = [
            "message" => 'Can\'t create Invoice for the order! Reason: ',
            "code" => 200
        ];

        //check if all the charges are in a state that order can be paid.
        //@todo since this is a business rule related to order payment,
        //      it should be moved to a more fitting place.
       $this->canBePaid($order);

        $webhookOrder = $webhook->getEntity();
        $webhookOrder->setId($order->getId());

        foreach ($order->getCharges() as $charge) {
            $webhookOrder->updateCharge($charge);
        }

        $orderHandlerService = new OrderHandler();
        $cantCreateReason = $orderHandlerService->handle($webhookOrder);

        $result["message"] .= $cantCreateReason;
        if ($cantCreateReason === true) {
            $result = [
                "message" => 'Order paid and invoice created.',
                "code" => 200
            ];
        }

        return $result;
    }

    protected function handleCanceled(Webhook $webhook)
    {
        $result = [
            "message" => 'Order can\'t be canceled! Reason: ',
            "code" => 200
        ];

        $order = $this->order;

        if($order->getStatus()->equals(OrderStatus::canceled())) {
            $result = [
                "message" => "It is not possible to cancel an order that was already canceled.",
                "code" => 200
            ];
            return $result;
        }

        $invoiceService = new InvoiceService();
        $invoiceService->cancelInvoicesFor($order);

        $order->setStatus(OrderStatus::canceled());
        $order->getPlatformOrder()->setState(OrderState::canceled());

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $i18n = new LocalizationService();
        $history = $i18n->getDashboard(
            'Order canceled.'
        );

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $statusOrderLabel = $order->getPlatformOrder()->getStatusLabel(
            $order->getStatus()
        );

        $messageComplementEmail = $i18n->getDashboard(
            'New order status: %s',
            $statusOrderLabel
        );

        $sender = $order->getPlatformOrder()->sendEmail($messageComplementEmail);
        $order->getPlatformOrder()->addHistoryComment($history, $sender);

        $result = [
            "message" => 'Order canceled.',
            "code" => 200
        ];

        return $result;
    }

    protected function handlePaymentFailed(Webhook $webhook)
    {
        $i18n = new LocalizationService();

        $history = $i18n->getDashboard(
            'Order payment failed'
        );
        $history .= '. ' . $i18n->getDashboard(
            'The order will be canceled'
        ) . '.';

        $this->order->getPlatformOrder()->addHistoryComment($history);

        return $this->handleCanceled($webhook);
    }

    //@todo handleCreated
    protected function handleCreated_TODO(Webhook $webhook)
    {
        //@todo, but not with priority,
    }

    //@todo handleClosed
    protected function handleClosed_TODO(Webhook $webhook)
    {
        //@todo, but not with priority,
    }


    /**
     *
     * @param  Webhook $webhook
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    protected function loadOrder(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();
        /**
         *
         * @var Order $order
        */
        $order = $webhook->getEntity();
        $order = $orderRepository->findByMundipaggId($order->getMundipaggId());

        if ($order === null) {

            $orderDecoratorClass =
                MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

            /**
             *
             * @var Order $webhookOrder
            */
            $webhookOrder = $webhook->getEntity();
            /**
             *
             * @var PlatformOrderInterface $order
             */
            $order = new $orderDecoratorClass();
            $order->loadByIncrementId($webhookOrder->getCode());

            if ($order->getIncrementId() === null) {
                throw new NotFoundException(
                    "Order Not found!"
                );
            }

            $orderFactory = new OrderFactory();
            $order = $orderFactory->createFromPlatformData(
                $order,
                $webhookOrder->getMundipaggId()->getValue()
            );
        }

        $this->order = $order;
    }

    private function canBePaid($order)
    {
        $unpayableChargeStatuses = [
            ChargeStatus::PENDING,
            ChargeStatus::PROCESSING
        ];

        $canBePaid = true;
        $chargesStatuses = [];
        foreach ($order->getCharges() as $charge) {
            $chargeStatus = $charge->getStatus()->getStatus();
            $chargesStatuses[$charge->getMundipaggId()->getValue()] = $chargeStatus;
            if (in_array($chargeStatus, $unpayableChargeStatuses)) {
                $canBePaid = false;
            }
        }

        if ($canBePaid === false) {
            $chargesStatuses = json_encode($chargesStatuses);
            throw new UnprocessableWebhookException(
                "One or more charges of the order are in a state that is not compatible with an paid order. Charge Statuses: $chargesStatuses"
            );
        }
    }
}