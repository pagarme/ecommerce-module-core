<?php

namespace Mundipagg\Core\Kernel\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\ValueObjects\SubscriptionStatus;
use Mundipagg\Core\Payment\Traits\WithCustomerTrait;
use Mundipagg\Core\Recurrence\Aggregates\Cycle;

final class Subscription extends AbstractEntity
{
    use WithCustomerTrait;

    /**
     *
     * @var PlatformOrderInterface 
     */
    private $platformOrder;

    /**
     *
     * @var SubscriptionStatus
     */
    private $status;

    private $cycle;

    /**
     *
     * @return PlatformOrderInterface
     */
    public function getPlatformOrder()
    {
        return $this->platformOrder;
    }

    /**
     *
     * @param  PlatformOrderInterface $platformOrder
     * @return Order
     */
    public function setPlatformOrder(PlatformOrderInterface $platformOrder)
    {
        $this->platformOrder = $platformOrder;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCode()
    {
        return $this->platformOrder->getCode();
    }

    /**
     *
     * @return SubscriptionStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @param  SubscriptionStatus $status
     * @return $this
     */
    public function setStatus(SubscriptionStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     *
     * @return Cycle
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     *
     * @param  Cycle $cycle
     * @return $this
     */
    public function setCycle(Cycle $cycle)
    {
        $this->cycle = $cycle;
        return $this;
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
        $obj = new \stdClass();

        $obj->id = $this->getId();
        $obj->mundipaggId = $this->getMundipaggId();
        $obj->code = $this->getCode();
        $obj->status = $this->getStatus();
        $obj->customer = $this->getCustomer();

        return $obj;
    }
}