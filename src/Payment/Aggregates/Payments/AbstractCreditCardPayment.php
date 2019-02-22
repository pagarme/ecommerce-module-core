<?php

namespace Mundipagg\Core\Payment\Aggregates\Payments;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Mundipagg\Core\Payment\ValueObjects\PaymentMethod;

abstract class AbstractCreditCardPayment extends AbstractPayment
{
    /** @var int */
    protected $installments;
    /** @var string */
    protected $statementDescriptor;
    /** @var boolean */
    protected $capture;
    /** @var AbstractCardIdentifier */
    protected $identifier;


    public function __construct()
    {
        $this->installments = 1;
        $this->capture = true;
    }

    /**
     * @return int
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @param int $installments
     */
    public function setInstallments(int $installments)
    {
        if ($installments < 1) {
            throw new InvalidParamException(
                "Installments should be at least 1",
                $installments
            );
        }
        $this->installments = $installments;
    }

    /**
     * @return string
     */
    public function getStatementDescriptor()
    {
        return $this->statementDescriptor;
    }

    /**
     * @param string $statementDescriptor
     */
    public function setStatementDescriptor($statementDescriptor)
    {
        $this->statementDescriptor = $statementDescriptor;
    }

    /**
     * @return bool
     */
    public function isCapture()
    {
        return $this->capture;
    }

    /**
     * @param bool $capture
     */
    public function setCapture($capture)
    {
        $this->capture = $capture;
    }

    /**
     * @return AbstractCardIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function jsonSerialize()
    {
        $obj =  parent::jsonSerialize();

        $obj->installments = $this->installments;
        $obj->statementDescriptor = $this->statementDescriptor;
        $obj->capture = $this->capture;
        $obj->identifier = $this->identifier;

        return $obj;
    }


    /**
     * @param AbstractCardIdentifier $identifier
     */
    abstract public function setIdentifier(AbstractCardIdentifier $identifier);

    static public function getBaseCode()
    {
        return PaymentMethod::creditCard()->getMethod();
    }

}