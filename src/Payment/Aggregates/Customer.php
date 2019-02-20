<?php

namespace Mundipagg\Core\Payment\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Payment\ValueObjects\CustomerPhones;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;

final class Customer extends AbstractEntity
{
    /** @var null|string */
    private $code;
    /** @var string */
    private $name;
    /** @var string */
    private $email;
    /** @var CustomerPhones */
    private $phones;
    /** @var string */
    private $document;
    /** @var CustomerType */
    private $type;
    /** @var Address */
    private $address;

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return CustomerPhones
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param CustomerPhones $phones
     */
    public function setPhones(CustomerPhones $phones)
    {
        $this->phones = $phones;
    }

    /**
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param string $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * @return CustomerType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param CustomerType $type
     */
    public function setType(CustomerType $type)
    {
        $this->type = $type;
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

        $code = $this->code;
        if ($code !== null) {
            $obj->code = $code;
        }

        $obj->name = $this->name;
        $obj->email = $this->email;
        $obj->phones = $this->phones;
        $obj->document = $this->document;
        $obj->type = $this->type;
        $obj->address = $this->address;

        return $obj;
    }
}