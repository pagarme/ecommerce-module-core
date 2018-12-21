<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Interfaces\PlatformInvoiceInterface;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;

class InvoiceService
{

    /**
     * @param PlatformOrderInterface $order
     * @return PlatformInvoiceInterface
     */
    public function createInvoiceFor(PlatformOrderInterface $order)
    {
        $localizationService = new LocalizationService();
        $platformInvoiceDecoratorClass =
            MPSetup::get(
                MPSetup::CONCRETE_PLATFORM_INVOICE_DECORATOR_CLASS
            );
        /** @var PlatformInvoiceInterface $invoice */
        $invoice = new $platformInvoiceDecoratorClass();
        $invoice->createFor($order);

        $message = $localizationService->getDashboard(
            'Invoice created: #%d.',
            $invoice->getIncrementId()
        );
        $order->addHistoryComment($message);
        $order->save();

        return $invoice;
    }
}