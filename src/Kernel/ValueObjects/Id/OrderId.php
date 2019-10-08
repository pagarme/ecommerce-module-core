<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Id;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class OrderId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^or_\w{16}$/', $value) === 1;
    }
}