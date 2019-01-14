<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Key;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

final class TestPublicKey extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/pk_test_\w{16}$/', $value) === 1;
    }
}