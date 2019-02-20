<?php

namespace Mundipagg\Core\Payment\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Payment\Traits\WithAmountTrait;
use Mundipagg\Core\Payment\ValueObjects\Phone;

final class Shipping extends AbstractEntity
{
    use WithAmountTrait;

    /** @var string */
    private $description;
    /** @var string */
    private $recipientName;
    /** @var Phone */
    private $recipientPhone;
    /** @var Address */
    private $address;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getRecipientName()
    {
        return $this->recipientName;
    }

    /**
     * @param string $recipientName
     */
    public function setRecipientName($recipientName)
    {
        $this->recipientName = $recipientName;
    }

    /**
     * @return Phone
     */
    public function getRecipientPhone()
    {
        return $this->recipientPhone;
    }

    /**
     * @param Phone $recipientPhone
     */
    public function setRecipientPhone(Phone $recipientPhone)
    {
        $this->recipientPhone = $recipientPhone;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
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

        $obj->amount = $this->amount;
        $obj->description = $this->description;
        $obj->recipientName = $this->recipientName;
        $obj->recipientPhone = $this->recipientPhone;
        $obj->address = $this->address;

        return $obj;
    }
}