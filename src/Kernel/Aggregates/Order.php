<?php

namespace Mundipagg\Core\Kernel\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Payment\Traits\WithCustomerTrait;

final class Order extends AbstractEntity
{
    use WithCustomerTrait;

    /**
     *
     * @var PlatformOrderInterface 
     */
    private $platformOrder;

    /**
     *
     * @var OrderStatus 
     */
    private $status;
    /**
     *
     * @var Charge[] 
     */
    private $charges;

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
     * @return int
     */
    public function getAmount()
    {
        $amount = 0;
        foreach ($this->getCharges() as $charge) {
            $amount += $charge->getAmount();
        }
        return $amount;
    }

    /**
     *
     * @return OrderStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @param  OrderStatus $status
     * @return Order
     */
    public function setStatus(OrderStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     *
     * @return Charge[]
     */
    public function getCharges()
    {
        if (!is_array($this->charges)) {
            return [];
        }
        return $this->charges;
    }

    /**
     *
     * @param  Charge $newCharge
     * @return Order
     */
    public function addCharge(Charge $newCharge)
    {
        $charges = $this->getCharges();
        //cant add a charge that was already added.
        foreach ($charges as $charge) {
            if ($charge->getMundipaggId()->equals(
                $newCharge->getMundipaggId()
            )
            ) {
                return $this;
            }
        }

        $charges[] = $newCharge;
        $this->charges = $charges;

        return $this;  
    }

    public function updateCharge(Charge $updatedCharge, $overwriteId = false)
    {
        $charges = $this->getCharges();

        foreach ($charges as &$charge) {
            if ($charge->getMundipaggId()->equals($updatedCharge->getMundipaggId())) {
                $chargeId = $charge->getId();
                $charge = $updatedCharge;
                if ($overwriteId) {
                    $charge->setId($chargeId);
                }
                $this->charges = $charges;
                return;
            }
        }

        $this->addCharge($updatedCharge);
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
        $obj->amount = $this->getAmount();
        $obj->status = $this->getStatus();
        $obj->charges = $this->getCharges();
        $obj->customer = $this->getCustomer();

        return $obj;
    }
}