<?php

namespace Mundipagg\Core\Recurrence\ValueObjects;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class PlanItemId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^pi_\w{16}$/', $value) === 1;
    }
}