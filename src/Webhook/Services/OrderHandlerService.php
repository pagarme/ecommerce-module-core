<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractPlatformOrderDecorator;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

final class OrderHandlerService extends AbstractHandlerService
{
    /** @var AbstractPlatformOrderDecorator */
    private $orderDecorator;

    public function __construct()
    {
    }

    protected function handlePaid(Webhook $webhook)
    {
        $orderDecorator = $this->orderDecorator;
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