<?php

namespace Mundipagg\Core\Payment\Services\ResponseHandlers;

use Mundipagg\Core\Kernel\Abstractions\AbstractDataService;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\InvoiceService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\InvoiceState;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Payment\Interfaces\ResponseHandlerInterface;

/** For possible order states, see https://docs.mundipagg.com/v1/reference#pedidos */
final class OrderHandler implements ResponseHandlerInterface
{

    /**
     * @param Order $order
     */
    public function handle($order)
    {
        $statusHandler =
            'handleOrderStatus' .
            ucfirst($order->getStatus()->getStatus());

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $this->$statusHandler($order);

    }

    private function handleOrderStatusPending(Order $order)
    {
        $a = 1;
    }

    private function handleOrderStatusPaid(Order $order)
    {
        $invoiceService = new InvoiceService();

        $cantCreateReason = $invoiceService->getInvoiceCantBeCreatedReason($order);
        $invoice = $invoiceService->createInvoiceFor($order);
        if ($invoice !== null) {
            $invoice->setState(InvoiceState::paid());
            $invoice->save();
            $platformOrder = $order->getPlatformOrder();

            $this->createCaptureTransaction($order);

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
        }
    }

    private function createCaptureTransaction(Order $order)
    {
        $dataServiceClass =
            MPSetup::get(MPSetup::CONCRETE_DATA_SERVICE);

        /**
         *
         * @var AbstractDataService $dataService
         */
        $dataService = new $dataServiceClass();
        $dataService->createCaptureTransaction($order);
    }

    private function handleOrderStatusCanceled(Order $order)
    {
        $a = 1;
    }

    private function handleOrderStatusFailed(Order $order)
    {
        $a = 1;
    }

}