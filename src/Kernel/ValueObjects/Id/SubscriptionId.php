<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Id;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class SubscriptionId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^sub_\w{16}$/', $value) === 1;
    }
}
