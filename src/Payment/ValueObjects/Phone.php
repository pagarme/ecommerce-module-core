<?php

namespace Mundipagg\Core\Payment\ValueObjects;


use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;
use Mundipagg\Core\Kernel\ValueObjects\NumericString;

final class Phone extends AbstractValueObject
{
    /** @var NumericString */
    private $countryCode;
    /** @var NumericString */
    private $areaCode;
    /** @var NumericString */
    private $number;

    /**
     * Phone constructor.
     * @param string $countryCode
     * @param string $areaCode
     * @param string $number
     */
    public function __construct($countryCode, $areaCode, $number)
    {
        $this->countryCode =
            new NumericString(preg_replace('/(?!\d)./', '', $countryCode));
        $this->areaCode =
            new NumericString(preg_replace('/(?!\d)./', '', $areaCode));
        $this->number =
            new NumericString(preg_replace('/(?!\d)./', '', $number));
    }

    /**
     * @return NumericString
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return NumericString
     */
    public function getAreaCode()
    {
        return $this->areaCode;
    }

    /**
     * @return NumericString
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param Phone $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getCountryCode()->equals($object->getCountryCode()) &&
            $this->getAreaCode()->equals($object->getAreaCode()) &&
            $this->getNumber()->equals($object->getNumber())
        ;
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

        $obj->countryCode = $this->getCountryCode();
        $obj->areaCode = $this->getAreaCode();
        $obj->number = $this->getNumber();

        return $obj;
    }
}