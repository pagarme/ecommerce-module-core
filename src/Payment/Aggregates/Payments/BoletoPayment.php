<?php

namespace Mundipagg\Core\Payment\Aggregates\Payments;

use MundiAPILib\Models\CreateBoletoPaymentRequest;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Mundipagg\Core\Payment\ValueObjects\BoletoBank;
use Mundipagg\Core\Payment\ValueObjects\PaymentMethod;

final class BoletoPayment extends AbstractPayment
{
    /** @var BoletoBank */
    private $bank;
    /** @var string */
    private $instructions;

    /**
     * @return BoletoBank
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param BoletoBank $bank
     */
    public function setBank(BoletoBank $bank)
    {
        $this->bank = $bank;
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * @param string $instructions
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;
    }

    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->bank = $this->bank;
        $obj->instructions = $this->instructions;

        return $obj;
    }

    static public function getBaseCode()
    {
        return PaymentMethod::boleto()->getMethod();
    }

    /**
     * @return CreateBoletoPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $paymentRequest = new CreateBoletoPaymentRequest();

        $paymentRequest->bank = $this->getBank()->getCode();
        $paymentRequest->instructions = $this->getInstructions();

        return $paymentRequest;
    }
}