<?php

namespace Mundipagg\Core\Recurrence\ValueObjects;

use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;
use Unirest\Exception;

class DiscountValueObject extends AbstractValueObject
{
    const DISCOUNT_TYPE_FLAT = 'flat';
    const DISCOUNT_TYPE_PERCENT = 'percentage';

    /** @var string */
    protected $discountType;
    /** @var float */
    protected $discountValue;

    protected function __construct($type, $value)
    {
        $this->setDiscountType($type);
        $this->setDiscountValue($value);
    }

    /**
     * @param $value
     * @return DiscountValueObject
     */
    public static function flat($value)
    {
        return new DiscountValueObject(
            self::DISCOUNT_TYPE_FLAT,
            $value
        );
    }

    /**
     * @param $value
     * @return DiscountValueObject
     */
    public static function percentage($value)
    {
        return new DiscountValueObject(
            self::DISCOUNT_TYPE_PERCENT,
            $value
        );
    }

    /**
     * @return float
     */
    public function getDiscountValue()
    {
        return $this->discountValue;
    }

    /**
     * @param float $discountValue
     * @return DiscountValueObject
     * @throws Exception
     */
    protected function setDiscountValue($discountValue)
    {
        if ($discountValue < 0) {
            throw new Exception(
                "Interval discount should be greater than 0: $discountValue!"
            );
        }

        $this->discountValue = $discountValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * @param string $discountType
     * @return DiscountValueObject
     */
    private function setDiscountType($discountType)
    {
        $this->discountType = $discountType;
        return $this;
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
            $this->getDiscountType() === $object->getDiscountType() &&
            $this->getDiscountValue() === $object->getDiscountValue();
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
          'discountType' => $this->getDiscountType(),
          'discountValue' => $this->getDiscountValue()
        ];
    }
}