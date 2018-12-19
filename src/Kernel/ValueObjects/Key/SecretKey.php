<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Key;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

final class SecretKey extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/sk_\w{16}$/', $value) === 1;
    }
}