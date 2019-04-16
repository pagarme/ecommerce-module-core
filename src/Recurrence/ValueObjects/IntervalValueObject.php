<?php

namespace Mundipagg\Core\Recurrence\ValueObjects;

use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;
use Unirest\Exception;

class IntervalValueObject extends AbstractValueObject
{
    const INTERVAL_TYPE_WEEK = 'week';
    const INTERVAL_TYPE_MONTH = 'month';
    const INTERVAL_TYPE_YEAR = 'year';

    /** @var int */
    protected $frequency;
    /** @var string */
    protected $intervalType;

    protected function __construct($type, $frequency)
    {
        $this->setIntervalType($type);
        $this->setFrequency($frequency);
    }

    public static function week($frequency)
    {
        return new IntervalValueObject(
            self::INTERVAL_TYPE_WEEK,
            $frequency
        );
    }

    public static function month($frequency)
    {
        return new IntervalValueObject(
            self::INTERVAL_TYPE_MONTH,
            $frequency
        );
    }

    public static function year($frequency)
    {
        return new IntervalValueObject(
            self::INTERVAL_TYPE_YEAR,
            $frequency
        );
    }

    /**
     * @return int
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return IntervalValueObject
     * @throws Exception
     */
    private function setFrequency($frequency)
    {
        $intValue = intval($frequency);
        if ($intValue <= 0) {
            throw new Exception(
                "Interval frequency should be greater than 0: $frequency!"
            );
        }
        $this->frequency = $intValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntervalType()
    {
        return $this->intervalType;
    }

    /**
     * @param string $intervalType
     */
    private function setIntervalType($intervalType)
    {
        $this->intervalType = $intervalType;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getIntervalType() === $object->getIntervalType() &&
            $this->getFrequency() === $object->getFrequency();
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
            'intervalType' => $this->getIntervalType(),
            'frequency' => $this->getFrequency()
        ];
    }
}