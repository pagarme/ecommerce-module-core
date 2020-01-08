<?php

namespace Mundipagg\Core\Recurrence\ValueObjects;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class InvoiceIdValueObject extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/in_\w{16}$/', $value) === 1;
    }
}