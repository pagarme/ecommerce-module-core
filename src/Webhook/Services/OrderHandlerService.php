<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Services\InvoiceService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

final class OrderHandlerService extends AbstractHandlerService
{
    protected function handlePaid(Webhook $webhook)
    {
        $order = $this->order;

        $result = [
            "message" => 'Can\'t create Invoice for the order!',
            "code" => 200
        ];
        if($order->canInvoice()) {
            $invoiceService = new InvoiceService();
            $i18n = new LocalizationService();

            $invoiceService->createInvoiceFor($order);
            $order->setState(OrderState::processing());
            $order->setStatus(OrderStatus::processing());
            $order->addHistoryComment(
                $i18n->getDashboard('Order paid.')
            );
            $order->save();

            $result = [
                "message" => 'Order paid and invoice created.',
                "code" => 200
            ];
        }
        return $result;
    }

    protected function loadOrder($webhook)
    {
        $orderDecoratorClass =
            MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

        /**
         *
         * @var PlatformOrderInterface $order
        */
        $order = new $orderDecoratorClass();
        $order->loadByIncrementId($webhook->getEntity()->getCode());
        $this->order = $order;
    }
}