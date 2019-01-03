<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Interfaces\PlatformInvoiceInterface;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;

class InvoiceService
{

    /**
     *
     * @param  Order $platformOrder
     * @return null|PlatformInvoiceInterface
     */
    public function createInvoiceFor(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();

        if (!$platformOrder->canInvoice()) {
            return null;
        }

        $localizationService = new LocalizationService();
        $platformInvoiceDecoratorClass =
            MPSetup::get(
                MPSetup::CONCRETE_PLATFORM_INVOICE_DECORATOR_CLASS
            );
        /**
         *
         * @var PlatformInvoiceInterface $invoice
        */
        $invoice = new $platformInvoiceDecoratorClass();
        $invoice->createFor($platformOrder);

        $message = $localizationService->getDashboard(
            'Invoice created: #%d.',
            $invoice->getIncrementId()
        );
        $platformOrder->addHistoryComment($message);
        $platformOrder->save();

        return $invoice;
    }

    /**
     * This method is based on the original canInvoice method of Magento2.
     *
     * @see Magento\Sales\Model\Order::canInvoice;
     * @param Order $order
     * @return null|string
     */
    public function getInvoiceCantBeCreatedReason(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();

        if ($platformOrder->canUnhold()) {
           return 'canUnhold';
        }

        if ($platformOrder->isPaymentReview()) {
            return 'isPaymentReview';
        }

        $state = $platformOrder->getState();
        if ($platformOrder->isCanceled()) {
            return 'Order is Canceled';
        }

        if ($state->equals(OrderState::complete())) {
            return 'Order is Complete';
        }

        if ($state->equals(OrderState::closed())) {
            return 'Order is Closed';
        }

        /** @todo How can we do this conditions decoupled of the platform?

        if ($platformOrder->getActionFlag(self::ACTION_FLAG_INVOICE) === false) {
            return false;
        }

        foreach ($platformOrder->getAllItems() as $item) {
            if ($item->getQtyToInvoice() > 0 && !$item->getLockedDoInvoice()) {
                return true;
            }
        }
        return false;
         */

        return 'No items to be invoiced or M2 Action Flag Invoice is false';
    }
}