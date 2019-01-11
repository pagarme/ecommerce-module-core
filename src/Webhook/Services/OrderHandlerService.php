<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\InvoiceService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\InvoiceState;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

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

        $invoiceService = new InvoiceService();
        $cantCreateReason = $invoiceService->getInvoiceCantBeCreatedReason($order);
        $result["message"] .= $cantCreateReason;

        $invoice = $invoiceService->createInvoiceFor($order);
        if ($invoice !== null) {
            $invoice->setState(InvoiceState::paid());
            $invoice->save();
            $platformOrder = $order->getPlatformOrder();

            $order->setStatus(OrderStatus::processing());
            //@todo maybe an Order Aggregate should have a State too.
            $platformOrder->setState(OrderState::processing());

            $i18n = new LocalizationService();
            $platformOrder->addHistoryComment(
                $i18n->getDashboard('Order paid.')
            );

            $orderRepository = new OrderRepository();
            $orderRepository->save($order);

            $orderService = new OrderService();
            $orderService->syncPlatformWith($order);

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

        $invoiceService = new InvoiceService();
        $invoiceService->cancelInvoicesFor($order);

        $order->setStatus(OrderStatus::canceled());

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $i18n = new LocalizationService();
        $history = $i18n->getDashboard(
            'Order canceled.'
        );
        $order->getPlatformOrder()->addHistoryComment($history);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $result = [
            "message" => 'Order canceled.',
            "code" => 200
        ];
        return $result;
    }

    //@todo handlePaymentFailed
    protected function handlePaymentFailed_TODO(Webhook $webhook)
    {
        //@todo
        //In simulator, Occurs with values between 1.051,72 and 1.262,06, auth
        // only and auth and capture.
        //AcquirerMessage = Simulator|Transação de simulação negada por falta de crédito, utilizado para realizar simulação de autorização parcial
        //occurs in the next case of the simulator too.

        //When this webhook is received, the order wasn't created on magento, so
        // no further action is needed.
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
     * @param Webhook $webhook
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

            /** @var Order $webhookOrder */
            $webhookOrder = $webhook->getEntity();
            /**
             *
             * @var PlatformOrderInterface $order
             */
            $order = new $orderDecoratorClass();
            $order->loadByIncrementId($webhookOrder->getCode());


            $orderFactory = new OrderFactory();
            $order = $orderFactory->createFromPlatformData(
                $order,
                $webhookOrder->getMundipaggId()->getValue()
            );
        }

        $this->order = $order;
    }
}