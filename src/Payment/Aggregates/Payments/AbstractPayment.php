<?php

namespace Mundipagg\Core\Payment\Aggregates\Payments;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Payment\Traits\WithAmountTrait;
use Mundipagg\Core\Payment\Traits\WithCustomerTrait;
use Mundipagg\Core\Payment\ValueObjects\PaymentMethod;

abstract class AbstractPayment extends AbstractEntity
{
    use WithAmountTrait;
    use WithCustomerTrait;

    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->paymentMethod = static::getBaseCode();
        $obj->amount = $this->getAmount();

        $customer = $this->getCustomer();
        if ($customer !== null) {
            $obj->customer = $customer;
        }

        return $obj;
    }

    abstract static public function getBaseCode();
}