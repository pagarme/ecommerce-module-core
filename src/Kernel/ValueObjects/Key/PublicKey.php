<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Key;

final class PublicKey extends AbstractPublicKey
{
    protected function validateValue($value)
    {
        return preg_match('/pk_\w{16}$/', $value) === 1;
    }
}