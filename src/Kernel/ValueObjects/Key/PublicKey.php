<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Key;

use Mundipagg\Core\Kernel\Interfaces\SensibleDataInterface;

final class PublicKey extends AbstractPublicKey implements SensibleDataInterface
{
    protected function validateValue($value)
    {
        return preg_match('/^pk_\w{16}$/', $value) === 1;
    }
}