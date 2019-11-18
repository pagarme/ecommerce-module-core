<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Id;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class CycleId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^cycle_\w{16}$/', $value) === 1;
    }
}
