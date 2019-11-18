<?php

namespace Mundipagg\Core\Recurrence\ValueObjects;

use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;

final class SubscriptionStatus extends AbstractValueObject
{
    const ACTIVE = 'active';
    const CANCELED = 'canceled';
    const FUTURE = 'future';

    /**
     * @var string 
     */
    private $status;

    /**
     * SubscriptionStatus constructor.
     *
     * @param string $status
     */
    private function __construct($status)
    {
        $this->setStatus($status);
    }

    static public function active()
    {
        return new self(self::ACTIVE);
    }

    static public function future()
    {
        return new self(self::FUTURE);
    }

    /**
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @param  string $status
     * @return SubscriptionStatus
     */
    private function setStatus($status)
    {
        $this->status = $status;
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
        return $this->getStatus();
    }
}