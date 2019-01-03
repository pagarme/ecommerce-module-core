<?php

namespace Mundipagg\Core\Kernel\ValueObjects;

use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;

final class TransactionType extends AbstractValueObject
{
   const CREDIT_CARD = "credit_card";
    /**
     *
     * @var string 
     */
    private $type;

    /**
     * OrderStatus constructor.
     *
     * @param string $status
     */
    private function __construct($status)
    {
        $this->setType($status);
    }

    public static function creditCard()
    {
        return new self(self::CREDIT_CARD);
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param  string $status
     * @return OrderStatus
     */
    private function setType($status)
    {
        $this->type = $status;
        return $this;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  OrderStatus $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->getStatus() === $object->getStatus();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    public function jsonSerialize()
    {
        return $this->type;
    }
}