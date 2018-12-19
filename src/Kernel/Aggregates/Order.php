<?php

namespace Mundipagg\Core\Kernel\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;

final class Order extends AbstractEntity
{
    /** @var string */
    private $code;
    /** @var int */
    private $amount;
    /** @var OrderStatus */
    private $status;
    /** @var Charge[] */
    private $charges;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Order
     */
    public function setCode(string $code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Order
     */
    public function setAmount(int $amount)
    {
        if ($amount < 0) {
            throw new InvalidParamException("Amount should be greater or equal to 0", $amount);
        }

        $this->amount = $amount;
        return $this;
    }

    /**
     * @return OrderStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param OrderStatus $status
     * @return Order
     */
    public function setStatus(OrderStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return Charge[]
     */
    public function getCharges()
    {
        return $this->charges;
    }

    /**
     * @param Charge $charge
     * @return Order
     */
    public function addCharge($charge)
    {
        $this->charges[] = $charge;
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
        $obj->code = $this->getCode();
        $obj->amount = $this->getAmount();
        $obj->status = $this->getStatus();
        $obj->charges = $this->getCharges();

        return $obj;
    }
}