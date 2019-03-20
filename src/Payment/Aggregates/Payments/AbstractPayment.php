<?php

namespace Mundipagg\Core\Payment\Aggregates\Payments;

use MundiAPILib\Models\CreatePaymentRequest;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use Mundipagg\Core\Payment\Interfaces\HaveOrderInterface;
use Mundipagg\Core\Payment\Traits\WithAmountTrait;
use Mundipagg\Core\Payment\Traits\WithCustomerTrait;
use Mundipagg\Core\Payment\Traits\WithOrderTrait;

abstract class AbstractPayment
    extends AbstractEntity
    implements ConvertibleToSDKRequestsInterface, HaveOrderInterface
{
    use WithAmountTrait;
    use WithCustomerTrait;
    use WithOrderTrait;

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

    /**
     * @return CreatePaymentRequest
     */
    public function convertToSDKRequest()
    {
        $newPayment = new CreatePaymentRequest();
        $newPayment->amount = $this->getAmount();

        $primitive = static::getBaseCode();
        $newPayment->$primitive = $this->convertToPrimitivePaymentRequest();
        $newPayment->paymentMethod = $this->cammel2SnakeCase($primitive);

        return $newPayment;
    }

    abstract protected function convertToPrimitivePaymentRequest();

    private function cammel2SnakeCase($cammelCaseString)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $cammelCaseString, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}