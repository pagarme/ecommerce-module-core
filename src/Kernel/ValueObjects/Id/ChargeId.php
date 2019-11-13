<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Id;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class ChargeId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^ch_\w{16}$/', $value) === 1;
    }
}