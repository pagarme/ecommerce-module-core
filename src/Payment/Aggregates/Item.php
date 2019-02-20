<?php

namespace Mundipagg\Core\Payment\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Payment\Traits\WithAmountTrait;

final class Item extends AbstractEntity
{
    use WithAmountTrait;

    /** @var string */
    private $description;
    /** @var integer */
    private $quantity;

       /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @throws InvalidParamException
     */
    public function setQuantity(int $quantity)
    {
        if ($quantity <= 0) {
            throw new InvalidParamException(
                'Quantity should be greater than 0!',
                $quantity
            );
        }
        $this->quantity = $quantity;
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

        $obj->amount = $this->amount;
        $obj->description = $this->description;
        $obj->quantity = $this->quantity;

        return $obj;
    }
}