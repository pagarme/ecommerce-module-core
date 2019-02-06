<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Id;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class MerchantId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/merch_\w{16}$/', $value) === 1;
    }
}