<?php

namespace Mundipagg\Core\Payment\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

final class Address extends AbstractEntity
{
    const ADDRESS_LINE_SEPARATOR = ',';

    /**
     * @var string
     */
    private $number;
    /**
     * @var string
     */
    private $street;
    /**
     * @var string
     */
    private $neighborhood;
    /**
     * @var string
     */
    private $complement;
    /**
     * @var string
     */
    private $zipCode;
    /**
     * @var string
     */
    private $city;
    /**
     * @var string
     */
    private $country;

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = str_replace(
            self::ADDRESS_LINE_SEPARATOR,
            '',
            $number
        );
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = str_replace(
            self::ADDRESS_LINE_SEPARATOR,
            '',
            $street
        );
    }

    /**
     * @return string
     */
    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    /**
     * @param string $neighborhood
     */
    public function setNeighborhood($neighborhood)
    {
        $this->neighborhood = str_replace(
            self::ADDRESS_LINE_SEPARATOR,
            '',
            $neighborhood
        );
    }

    /**
     * @return string
     */
    public function getComplement()
    {
        return $this->complement;
    }

    /**
     * @param string $complement
     */
    public function setComplement($complement)
    {
        $this->complement = $complement;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getLine1()
    {
        $line = [];
        $line[] = $this->getNumber();
        $line[] = $this->getStreet();
        $line[] = $this->getNeighborhood();

        return implode (self::ADDRESS_LINE_SEPARATOR, $line);
    }

    public function getLine2()
    {
        return $this->complement;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return string data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->number = $this->number;
        $obj->street = $this->street;
        $obj->neighborhood = $this->neighborhood;
        $obj->complement = $this->complement;
        $obj->zipCode = $this->zipCode;
        $obj->city = $this->city;
        $obj->country = $this->country;
        $obj->line1 = $this->getLine1();
        $obj->line2 = $this->getLine2();
        
        return $obj;
    }
}