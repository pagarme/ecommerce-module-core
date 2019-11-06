<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

class SubProductSubscription extends AbstractEntity
{
    /** @var int */
    protected $id;
    /** @var int */
    protected $productSubscriptionId;
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
     * @return SubProductSubscription
     */
    public function setId($id)
    {
        $this->id = intval($id);
        return $this;
    }

    /**
     * @return int
     */
    public function getProductSubscriptionId()
    {
        return $this->productSubscriptionId;
    }

    /**
     * @param int $productSubscriptionId
     * @return SubProductSubscription
     */
    public function setProductSubscriptionId($productSubscriptionId)
    {
        $this->productSubscriptionId = intval($productSubscriptionId);
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
     * @return SubProductSubscription
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
     * @return SubProductSubscription
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
     * @return SubProductSubscription
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
     * @return SubProductSubscription
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
     * @return SubProductSubscription
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
            "productSubscriptionId" => $this->getProductSubscriptionId(),
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
     * @return SubProductSubscription
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }
}