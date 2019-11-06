<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

class SubProduct extends AbstractEntity
{
    /** @var int */
    protected $id;
    /** @var int */
    protected $productRecurrenceId;
    /** @var int */
    protected $productId;
    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var int */
    protected $price;
    /** @var int */
    protected $quantity;
    /** @var int */
    protected $cycles;
    /** @var string */
    protected $createdAt;
    /** @var string */
    protected $updatedAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return SubProduct
     */
    public function setId($id)
    {
        $this->id = intval($id);
        return $this;
    }

    /**
     * @return int
     */
    public function getProductRecurrenceId()
    {
        return $this->productRecurrenceId;
    }

    /**
     * @param int $productRecurrenceId
     * @return SubProduct
     */
    public function setProductRecurrenceId($productRecurrenceId)
    {
        $this->productRecurrenceId = intval($productRecurrenceId);
        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     * @return SubProduct
     */
    public function setProductId($productId)
    {
        $this->productId = intval($productId);
        return $this;
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
     * @return Template
     * @throws \Exception
     */
    public function setDescription($description)
    {
        if (preg_match('/[^a-zA-Z0-9 ]+/i', $description)) {
            throw new \Exception("The field description must not use special characters.");
        }

        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Template
     * @throws \Exception
     */
    public function setName($name)
    {
        if (preg_match('/[^a-zA-Z0-9 ]+/i', $name)) {
            throw new \Exception("The field name must not use special characters.");
        }

        $this->name = $name;
        return $this;
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
     * @return SubProduct
     */
    public function setQuantity($quantity)
    {
        $this->quantity = intval($quantity);
        return $this;
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
     * @return SubProduct
     */
    public function setCycles($cycles)
    {
        $this->cycles = intval($cycles);
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     * @return SubProduct
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string $updatedAt
     * @return SubProduct
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
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
            "id" => $this->getId(),
            "productRecurrenceId" => $this->getProductRecurrenceId(),
            "productId" => $this->getProductId(),
            "name" => $this->getName(),
            "description" => $this->getDescription(),
            "price" => $this->getPrice(),
            "cycles" => $this->getCycles(),
            "quantity" => $this->getQuantity(),
            "createdAt" => $this->getCreatedAt(),
            "updatedAt" => $this->getUpdatedAt(),
        ];
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Price in cents
     * @param int $price
     * @return SubProduct
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }
}