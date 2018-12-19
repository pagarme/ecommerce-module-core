<?php

namespace Mundipagg\Core\Kernel\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;

final class Charge extends AbstractEntity
{
    /** @var int */
    private $amount;
    /** @var int */
    private $paidAmount;
    /** @var string */
    private $code;
    /** @var ChargeStatus */
    private $status;

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Charge
     */
    public function setAmount(int $amount)
    {
        if ($amount < 0) {
            throw new InvalidParamException("Amount should be greater or equal to 0!", $amount);
        }
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return int
     */
    public function getPaidAmount()
    {
        return $this->paidAmount;
    }

    /**
     * @param int $paidAmount
     * @return Charge
     */
    public function setPaidAmount(int $paidAmount)
    {

        if ($paidAmount < 0) {
            throw new InvalidParamException("Paid Amount should be greater or equal to 0!", $paidAmount);
        }
        $this->paidAmount = $paidAmount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Charge
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return ChargeStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param ChargeStatus $status
     * @return Charge
     */
    public function setStatus(ChargeStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->id = $this->getId();
        $obj->amount = $this->getAmount();
        $obj->paidAmount = $this->getPaidAmount();
        $obj->code = $this->getCode();
        $obj->status = $this->getStatus();

        return $obj;
    }
}