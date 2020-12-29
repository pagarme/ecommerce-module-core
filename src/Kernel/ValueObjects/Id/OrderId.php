<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Id;

use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class OrderId extends AbstractValidString
{
    /**
     * OrderId string constructor.
     * @param $orderId
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function __construct($orderId)
    {
        parent::__construct($orderId);
    }

    protected function validateValue($value)
    {
        return preg_match('/^or_\w{16}$/', $value) === 1;
    }
}
