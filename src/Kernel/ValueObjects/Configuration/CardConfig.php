<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Configuration;

use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;

final class CardConfig extends AbstractValueObject
{
    /** @var bool */
    private $enabled;
    /** @var string */
    private $brand;
    /** @var int */
    private $maxInstallment;
    /** @var int */
    private $maxInstallmentWithoutInterest;
    /** @var float */
    private $initialInterest;
    /** @var float */
    private $incrementalInterest;

    /**
     * InstallmentConfigValueObject constructor.
     * @param $enabled
     * @param $brand
     * @param $maxInstallment
     * @param $maxInstallmentWithoutInterest
     * @param $initialInterest
     * @param $incrementalInterest
     * @throws InvalidParamException
     */
    public function __construct(
        $enabled,
        $brand,
        $maxInstallment,
        $maxInstallmentWithoutInterest,
        $initialInterest,
        $incrementalInterest
    )
    {
        $this->setEnabled($enabled);
        $this->setBrand($brand);
        $this->setMaxInstallment($maxInstallment);
        $this->setMaxInstallmentWithoutInterest($maxInstallmentWithoutInterest);
        $this->setInitialInterest($initialInterest);
        $this->setIncrementalInterest($incrementalInterest);
    }

    /**
     * @param bool $enabled
     */
    private function setEnabled($enabled)
    {
        $this->enabled = boolval($enabled);
        return $this;
    }

    /**
     * @param string $brand
     * @return CardConfig
     */
    private function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @param int $maxInstallment
     * @return CardConfig
     */
    private function setMaxInstallment($maxInstallment)
    {
        $newMaxInstallment = intval($maxInstallment);

        //just to validate individual times value;
        new Installment($newMaxInstallment, 1, 0);

        $this->maxInstallment = $newMaxInstallment;
        return $this;
    }

    /**
     * @param int $maxInstallmentWithoutInterest
     * @return CardConfig
     */
    private function setMaxInstallmentWithoutInterest($maxInstallmentWithoutInterest)
    {
        if ($this->maxInstallment === null) {
            throw new InvalidParamException(
                "'Max installment without interest' must be set after setting 'Max installment'!",
                $maxInstallmentWithoutInterest
            );
        }

        $newMaxInstallmentWithoutInterest = intval($maxInstallmentWithoutInterest);

        //just to validate individual times value;
        new Installment($newMaxInstallmentWithoutInterest, 1, 0);

        if ($newMaxInstallmentWithoutInterest > $this->maxInstallment) {
            throw new InvalidParamException(
                "'Max installment without interest' must be equal or smaller than 'Max Installments'! " ,
                $maxInstallmentWithoutInterest
            );
        }

        $this->maxInstallmentWithoutInterest = $newMaxInstallmentWithoutInterest;
        return $this;
    }

    /**
     * @param float $initialInterest
     * @return CardConfig
     */
    private function setInitialInterest($initialInterest)
    {
        $newInitialInterest = floatval($initialInterest);
        if ($newInitialInterest <= 0) {
            throw new InvalidParamException(
                "'Initial Interest' must be greater than 0! ",
                $initialInterest
            );
        }
        $this->initialInterest = $newInitialInterest;
        return $this;
    }

    /**
     * @param float $incrementalInterest
     * @return CardConfig
     */
    private function setIncrementalInterest($incrementalInterest)
    {
        $newIncrementalInterest = floatval($incrementalInterest);

        if ($newIncrementalInterest < 0) {
            throw new InvalidParamException(
                "'Incremental Interest' must be equal or greater than 0! ",
                $incrementalInterest
            );
        }

        $this->incrementalInterest = $newIncrementalInterest;
        return $this;
    }


    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }
    /**
     * @return int
     */
    public function getMaxInstallment()
    {
        return $this->maxInstallment;
    }
    /**
     * @return int
     */
    public function getMaxInstallmentWithoutInterest()
    {
        return $this->maxInstallmentWithoutInterest;
    }
    /**
     * @return float
     */
    public function getInitialInterest()
    {
        return $this->initialInterest;
    }

    /**
     * @return float
     */
    public function getIncrementalInterest()
    {
        return $this->incrementalInterest;
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
        return [
            "enabled" => $this->enabled,
            "brand" => $this->brand,
            "incrementalInterest" => $this->incrementalInterest,
            "initialInterest" => $this->initialInterest,
            "maxInstallment" => $this->maxInstallment,
            "maxInstallmentWithoutInterest" => $this->maxInstallmentWithoutInterest
        ];
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
            $this->enabled === $object->isEnabled() &&
            $this->brand === $object->getBrand() &&
            $this->maxInstallment === $object->getMaxInstallment() &&
            $this->maxInstallmentWithoutInterest === $object->getMaxInstallmentWithoutInterest() &&
            $this->initialInterest === $object->getInitialInterest() &&
            $this->incrementalInterest === $object->getIncrementalInterest()
            ;
    }
}