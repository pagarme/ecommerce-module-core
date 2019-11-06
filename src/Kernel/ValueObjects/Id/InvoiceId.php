<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Id;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class InvoiceId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^in_\w{16}$/', $value) === 1;
    }
}