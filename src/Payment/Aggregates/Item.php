<?php

namespace Mundipagg\Core\Payment\Aggregates;

use MundiAPILib\Models\CreateOrderItemRequest;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use Mundipagg\Core\Payment\Traits\WithAmountTrait;

final class Item extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;

    /** @var string */
    private $description;
    /** @var integer */
    private $quantity;
    /** @var string */
    private $code;

    private $selectedOption;

    private $type;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = substr($code, 0, 52);
    }

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
        $this->description = substr($description, 0, 256);
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
    public function setQuantity($quantity)
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
     * @return mixed
     */
    public function getSelectedOption()
    {
        return $this->selectedOption;
    }

    /**
     * @param mixed $selectedOption
     */
    public function setSelectedOption($selectedOption)
    {
        $this->selectedOption = $selectedOption;
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

    /**
     * @return CreateOrderItemRequest
     */
    public function convertToSDKRequest()
    {
        $itemRequest = new CreateOrderItemRequest();

        $code = $this->getCode();
        $amount = $this->getAmount();
        $quantity = $this->getQuantity();
        $description = $this->getDescription();

        if ($this->isQuantityFloat()) {
            $description .= " ($quantity * $amount)";
            $amount = ($this->roundUp(($amount/100) * $quantity, 2)) * 100;
            $quantity = 1;
        }

        $itemRequest->description = $description;
        $itemRequest->amount = $amount;
        $itemRequest->quantity = $quantity;
        $itemRequest->code = $code;

        return $itemRequest;
    }

    private function isQuantityFloat()
    {
        return (($this->quantity * 100) % 100) > 0;
    }

    private function roundUp ($value, $precision)
    {
        $pow = pow(10, $precision);
        return (ceil($pow*$value)+ceil($pow*$value-ceil($pow*$value)))/$pow;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }
}