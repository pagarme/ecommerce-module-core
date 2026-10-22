<?php

namespace Pagarme\Core\Kernel\ValueObjects\Key;

final class HubAccessTokenKey extends AbstractSecretKey
{
    protected function validateValue($value)
    {
        return strpos($value ?? '', 'acs_') === 0;
    }
}
