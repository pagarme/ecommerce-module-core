<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\AbstractPlatformOrderDecorator;

class Order
{
    public function handlePaid(AbstractPlatformOrderDecorator $orderDecorator, $webHookData)
    {
        $result = [];
        if($orderDecorator->canInvoice()) {
            $invoice = $this->createInvoice($orderDecorator->getPlatformOrder());
            $result[] = [
                "order" => "canInvoice",
                "invoice" => $invoice,
            ];
        }
        return $result;
    }
}