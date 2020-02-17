<?php

namespace Mundipagg\Core\Webhook\Exceptions;

use Mundipagg\Core\Kernel\Exceptions\AbstractMundipaggCoreException;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

class UnprocessableWebhookException extends AbstractMundipaggCoreException
{
    /**
     * UnprocessableWebhookException constructor.
     */
    public function __construct($message, $code = 422)
    {
        parent::__construct($message, $code);
    }
}