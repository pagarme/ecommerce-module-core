<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class Repetition extends AbstractEntity
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var DiscountValueObject */
    protected $discount;
    /** @var IntervalValueObject */
    protected $interval;
    /** @var int */
    protected $subscriptionId;
    /** @var string */
    protected $createdAt;
    /** @var string */
    protected $updatedAt;

    /**
     * @return DiscountValueObject
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param DiscountValueObject $discount
     * @return Repetition
     */
    public function setDiscount(DiscountValueObject $discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return IntervalValueObject
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param IntervalValueObject $interval
     * @return Repetition
     */
    public function setInterval(IntervalValueObject $interval)
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return int
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @param int $subscriptionId
     * @return Repetition
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt->format(self::DATE_FORMAT);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt->format(self::DATE_FORMAT);
    }

    public function getDiscountType()
    {
        if ($this->getDiscount() !== null) {
            return $this->getDiscount()->getDiscountType();
        }
        return null;
    }

    public function getDiscountValue()
    {
        if ($this->getDiscount() !== null) {
            return $this->getDiscount()->getDiscountValue();
        }
        return null;
    }

    public function getIntervalCount()
    {
        if ($this->getInterval() !== null) {
            return $this->getInterval()->getIntervalCount();
        }
        return null;
    }

    public function getIntervalType()
    {
        if ($this->getInterval() !== null) {
            return $this->getInterval()->getIntervalType();
        }
        return null;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'subscriptionId' => $this->getSubscriptionId(),
            'intervalCount' => $this->getIntervalCount(),
            'intervalType' => $this->getIntervalType(),
            'discountValue' => $this->getDiscountValue(),
            'discountType' => $this->getDiscountType(),
            "createdAt" => $this->getCreatedAt(),
            "updatedAt" => $this->getUpdatedAt()
        ];
    }

    public function getIntervalTypeLabel()
    {
        //@todo change to a class formater maybe
        if ($this->getInterval()->getIntervalCount() > 1) {
            return $this->getInterval()->getIntervalType() . "s";
        }
        return $this->getInterval()->getIntervalType();
    }

    public static function getDiscountTypeSymbols()
    {
        //@todo get currency code from platform
        return [
            DiscountValueObject::DISCOUNT_TYPE_PERCENT => '%',
            DiscountValueObject::DISCOUNT_TYPE_FLAT => "R$"
        ];
    }
}