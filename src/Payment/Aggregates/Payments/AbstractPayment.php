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

    /** @var PaymentMethod */
    protected $paymentMethod;

    /**
     * @return PaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param PaymentMethod $paymentMethod
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->paymentMethod = $this->getPaymentMethod();
        $obj->amount = $this->getAmount();

        $customer = $this->getCustomer();
        if ($customer !== null) {
            $obj->customer = $customer;
        }

        return $obj;
    }
}