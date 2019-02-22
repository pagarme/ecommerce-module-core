<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Id;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class CustomerId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/cus_\w{16}$/', $value) === 1;
    }
}