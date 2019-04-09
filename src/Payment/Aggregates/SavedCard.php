<?php

namespace Mundipagg\Core\Payment\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Kernel\ValueObjects\NumericString;

final class SavedCard extends AbstractEntity
{
    /** @var CustomerId */
    private $ownerId;

    /** @var OwnerName */
    private $ownerName;

    /** @var NumericString */
    private $firstSixDigits;

    /** @var NumericString */
    private $lastFourDigits;

    /** @var CardBrand */
    private $brand;

    /**
     * @return CustomerId
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param CustomerId $ownerId
     */
    public function setOwnerId(CustomerId $ownerId)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @return OwnerName
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * @param OwnerName $ownerName
     */
    public function setOwnerName($ownerName)
    {
        $this->ownerName = $ownerName;
    }

    /**
     * @return NumericString
     */
    public function getFirstSixDigits()
    {
        return $this->firstSixDigits;
    }

    /**
     * @param NumericString $firstSixDigits
     */
    public function setFirstSixDigits($firstSixDigits)
    {
        $this->firstSixDigits = $firstSixDigits;
    }

    /**
     * @return NumericString
     */
    public function getLastFourDigits()
    {
        return $this->lastFourDigits;
    }

    /**
     * @param NumericString $lastFourDigits
     */
    public function setLastFourDigits(NumericString $lastFourDigits)
    {
        $this->lastFourDigits = $lastFourDigits;
    }

    /**
     * @return CardBrand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param CardBrand $brand
     */
    public function setBrand(CardBrand $brand)
    {
        $this->brand = $brand;
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

        $obj->id = $this->getId();
        $obj->mundipaggId = $this->getMundipaggId();
        $obj->ownerId = $this->getOwnerId();
        $obj->ownerName = $this->getOwnerName();
        $obj->firstSixDigits = $this->getFirstSixDigits();
        $obj->lastFourDigits = $this->getLastFourDigits();
        $obj->brand = $this->getBrand();

        return $obj;
    }
}