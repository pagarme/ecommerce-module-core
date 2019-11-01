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
    protected $intervalCount;
    /** @var string */
    protected $intervalType;

    protected function __construct($type, $intervalCount)
    {
        $this->setIntervalType($type);
        $this->setIntervalCount($intervalCount);
    }

    public static function week($intervalCount)
    {
        return new IntervalValueObject(
            self::INTERVAL_TYPE_WEEK,
            $intervalCount
        );
    }

    public static function month($intervalCount)
    {
        return new IntervalValueObject(
            self::INTERVAL_TYPE_MONTH,
            $intervalCount
        );
    }

    public static function year($intervalCount)
    {
        return new IntervalValueObject(
            self::INTERVAL_TYPE_YEAR,
            $intervalCount
        );
    }

    /**
     * @return int
     */
    public function getIntervalCount()
    {
        return $this->intervalCount;
    }

    /**
     * @param int $intervalCount
     * @return IntervalValueObject
     * @throws Exception
     */
    private function setIntervalCount($intervalCount)
    {
        $intValue = intval($intervalCount);
        if ($intValue <= 0) {
            throw new Exception(
                "Interval count should be greater than 0: $intervalCount!"
            );
        }
        $this->intervalCount = $intValue;
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
            $this->getIntervalCount() === $object->getIntervalCount();
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
            'intervalCount' => $this->getIntervalCount()
        ];
    }
}