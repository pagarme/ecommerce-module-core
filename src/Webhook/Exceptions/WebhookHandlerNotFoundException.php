<?php

namespace Mundipagg\Core\Webhook\Exceptions;

use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

class WebhookHandlerNotFoundException extends NotFoundException
{
    /**
     * WebhookHandlerNotFound constructor.
     */
    public function __construct(Webhook $webhook)
    {
        $message =
            "Handler for {$webhook->getType()->getEntityType()}." .
            "{$webhook->getType()->getAction()} webhook not found!";
        parent::__construct($message);
    }
}