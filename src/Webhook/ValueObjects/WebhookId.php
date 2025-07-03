<?php

namespace Pagarme\Core\Webhook\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class WebhookId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^hook_.+/', $value ?? '') === 1;
    }
}
