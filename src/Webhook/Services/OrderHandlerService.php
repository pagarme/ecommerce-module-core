<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

final class OrderHandlerService extends AbstractHandlerService
{
    public function __construct()
    {

    }

    protected function handlePaid(Webhook $webhook)
    {
        $order = $this->order;

        $result = [];
        if($order->canInvoice()) {
            $order->setState(OrderState::processing());
            $order->setStatus(OrderStatus::processing());
            $order->save();
            //@todo implement $invoice = $invoiceService->createInvoiceFor($order);
            //@todo $message = $localizationService->get('Invoice Created: #%1.', $invoice->getIncrementId());
            //@todo implement $order->addHistoryComment($message);

            /*$invoice = $this->createInvoice($order);
            $result[] = [
                "order" => "canInvoice",
                "invoice" => $invoice->getData(),
            ];*/
        }
        return $result;
    }

    protected function createInvoice($order)
    {
        $invoice = $this->getInvoiceService()->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->save();
        $transactionSave = $this->getTransaction()->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );
        $transactionSave->save();
        $this->getInvoiceSender()->send($invoice);

        $order->addStatusHistoryComment(
            __('Notified customer about invoice #%1.', $invoice->getIncrementId())
        )
            ->setIsCustomerNotified(true)
            ->save();

        $order->setState('processing')->setStatus('processing');
        $order->save();

        return $invoice->getData();
    }

    protected function loadOrder($webhook)
    {
        $orderDecoratorClass =
            MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

        /** @var PlatformOrderInterface $order */
        $order = new $orderDecoratorClass();
        $order->loadByIncrementId($webhook->getEntity()->getCode());
        $this->order = $order;
    }
}