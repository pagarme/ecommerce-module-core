<?php

namespace Mundipagg\Core\Webhook\Services;

class Order
{
    public function handlePaid(AbstractPlatformOrderDecorator $orderDecorator, $webHookData)
    {
        $result = [];
        if($order->canInvoice()) {
            $invoice = $this->createInvoice($orderDecorator->getPlatformOrder());
            $result[] = [
                "order" => "canInvoice",
                "invoice" => $invoice,
            ];
        }
        return $result;
    }
}