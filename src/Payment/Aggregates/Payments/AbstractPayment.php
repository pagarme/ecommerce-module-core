<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use PagarmeCoreApiLib\Models\CreatePaymentRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use Pagarme\Core\Payment\Interfaces\HaveOrderInterface;
use Pagarme\Core\Payment\Traits\WithAmountTrait;
use Pagarme\Core\Payment\Traits\WithCustomerTrait;
use Pagarme\Core\Payment\Traits\WithOrderTrait;

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

        $obj->orderCode = $this->order->getCode();
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

        if ($this->getCustomer() !== null) {
            $newPayment->customer = $this->getCustomer()->convertToSDKRequest();
        }

        $newPayment->split = static::getSplitData();
        $newPayment->metadata = static::getMetadata();
        return $newPayment;
    }

    abstract protected function convertToPrimitivePaymentRequest();

    protected function getSplitData()
    {
        $split1 = new \stdClass;
        $split1->amount = 10;
        $split1->recipient_id = "rp_lrb0q33Ta2cw9nBo";
        $split1->type = "percentage";
        $split1->options = new \stdClass;
        $split1->options->charge_processing_fee = true;
        $split1->options->charge_remainder_fee = true;
        $split1->options->liable = true;

        $split2 = new \stdClass;
        $split2->amount = 90;
        $split2->recipient_id = "rp_BR3QpoLFOiDZGVJK";
        $split2->type = "percentage";
        $split2->options = new \stdClass;
        $split2->options->charge_processing_fee = false;
        $split2->options->charge_remainder_fee = false;
        $split2->options->liable = false;
        return [$split1, $split2];
    }

    protected function getMetadata()
    {
        return null;
    }

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