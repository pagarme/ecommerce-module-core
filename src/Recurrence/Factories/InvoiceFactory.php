<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\InvoiceId;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;

class InvoiceFactory implements FactoryInterface
{
    public function createFromPostData($postData)
    {
        $postData = json_decode(json_encode($postData));
        $invoice = new Invoice();

        $invoice->setMundipaggId(new InvoiceId($postData->id));
        $invoice->setSubscriptionId(new SubscriptionId($postData->subscriptionId));

        return $invoice;
    }

    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }
}
