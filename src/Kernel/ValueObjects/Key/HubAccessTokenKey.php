<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Key;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

final class HubAccessTokenKey extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/\w{64}$/', $value) === 1;
    }
}