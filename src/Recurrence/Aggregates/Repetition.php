<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Exception;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class Repetition extends AbstractEntity
{
    /** @var DiscountValueObject */
    protected $discount;

    /** @var IntervalValueObject */
    protected $interval;

    /** @var int */
    protected $cycles;


    public function __construct()
    {
        $this->cycles = 0;
    }

    public function getDiscountValueLabel()
    {
        if ($this->getDiscount() === null) {
            return;
        }

        switch ($this->getDiscount()->getDiscountType()) {
            case DiscountValueObject::DISCOUNT_TYPE_FLAT:
                return "%s%.2f";
            case DiscountValueObject::DISCOUNT_TYPE_PERCENT:
                return"%s%.2f%%";
            default:
                return "Error: %s : %.2f";
        }
    }

    public function getIntervalTypeLabel()
    {
        //@todo change to a class formater maybe
        if ($this->getInterval()->getFrequency() > 1) {
            return $this->getInterval()->getIntervalType() . "s";
        }
        return $this->getInterval()->getIntervalType();
    }

    public function getIntervalTypeApiValue()
    {
        return $this->getInterval()->getIntervalType();
    }

    public static function getDiscountTypesArray()
    {
        //@todo get currency code from platform
        return [
            ['code'=>DiscountValueObject::DISCOUNT_TYPE_PERCENT, 'name' => '%'],
            ['code'=>DiscountValueObject::DISCOUNT_TYPE_FLAT, 'name' => "R$"]
        ];
    }

    public static function getIntervalTypesArray()
    {
        return [
            [
                'code'=>IntervalValueObject::INTERVAL_TYPE_WEEK,
                'name'=> "Semanal"
            ],
            [
                'code'=>IntervalValueObject::INTERVAL_TYPE_MONTH,
                'name'=> "Mensal"
            ],
            [
                'code'=>IntervalValueObject::INTERVAL_TYPE_YEAR,
                'name'=> "Anual"
            ]
        ];
    }

    public static function getValidIntervalTypes()
    {
        return [
            IntervalValueObject::INTERVAL_TYPE_WEEK,
            IntervalValueObject::INTERVAL_TYPE_MONTH,
            IntervalValueObject::INTERVAL_TYPE_YEAR
        ];
    }

    public static function getValidDiscountTypes()
    {
        return [
            DiscountValueObject::DISCOUNT_TYPE_PERCENT,
            DiscountValueObject::DISCOUNT_TYPE_FLAT
        ];
    }

    /**
     * @return int
     */
    public function getCycles()
    {
        return $this->cycles;
    }

    /**
     * @param int $cycles
     * @return RepetitionValueObject
     * @throws Exception
     */
    public function setCycles($cycles)
    {
        $newCycles = abs(intval($cycles));

        if ($newCycles < 1) {
            throw new Exception("The field cycles must be greater than or equal to 1 : $cycles");
        }

        $this->cycles = $newCycles;

        return $this;
    }

    public function toArray()
    {
        return [
            'cycles' => $this->getCycles(),
            'frequency' => $this->getFrequency(),
            'intervalType' => $this->getIntervalType(),
            'discountValue' => $this->getDiscountValue(),
            'discountType' => $this->getDiscountType(),
            'intervalTypeApiValue' => $this->getIntervalTypeApiValue()
        ];
    }

    /**
     * @param int $basePriceInCents
     * @return float|int
     */
    public function getDiscountPriceInCents($basePriceInCents)
    {
        if ($this->getDiscount()->getValue() <= 0) {
            return 0;
        }
        $percent = ($basePriceInCents * ($this->getDiscount()->getValue())) / 100;
        $fixed = $this->getDiscount()->getValue() * 100;

        if ($this->getDiscount()->getDiscountType() === DiscountValueObject::DISCOUNT_TYPE_PERCENT) {
            return $percent;
        }
        return $fixed;
    }

    /**
     * @param int $basePrice
     * @return float|int
     */
    public function getDiscountPrice($basePrice)
    {
        if ($this->getDiscount()->getValue() <= 0) {
            return 0;
        }
        $percent = ($basePrice * ($this->getDiscount()->getValue())) / 100;
        $fixed = $this->getDiscount()->getValue();

        if ($this->getDiscount()->getDiscountType() === DiscountValueObject::DISCOUNT_TYPE_PERCENT) {
            return $percent;
        }
        return $fixed;
    }

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

    public function getFrequency()
    {
        if ($this->getInterval() !== null) {
            return $this->getInterval()->getFrequency();
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
            'cycles' => $this->getCycles(),
            'frequency' => $this->getFrequency(),
            'intervalType' => $this->getIntervalType(),
            'discountValue' => $this->getDiscountValue(),
            'discountType' => $this->getDiscountType(),
            'intervalTypeApiValue' => $this->getIntervalTypeApiValue()
        ];
    }
}