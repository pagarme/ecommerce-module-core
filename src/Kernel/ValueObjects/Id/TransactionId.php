<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Id;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class TransactionId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/tran_\w{16}$/', $value) === 1;
    }
}