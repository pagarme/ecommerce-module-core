<?php

namespace Mundipagg\Core\Webhook\Exceptions;

use Mundipagg\Core\Kernel\Exceptions\AbstractMundipaggCoreException;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

class WebhookAlreadyHandledException extends AbstractMundipaggCoreException
{
    /**
     * WebhookHandlerNotFound constructor.
     */
    public function __construct(Webhook $webhook)
    {
        $message = "Webhoook {$webhook->getMundipaggId()->getValue()} already handled!";
        parent::__construct($message, 401);
    }
}