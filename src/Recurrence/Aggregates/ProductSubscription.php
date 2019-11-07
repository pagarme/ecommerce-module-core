<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;

class ProductSubscription extends AbstractEntity
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var int */
    protected $id = null;
    /** @var int */
    private $productId;
    /** @var boolean */
    private $creditCard = false;
    /** @var boolean */
    private $boleto = false;
    /** @var boolean */
    private $allowInstallments = false;
    /** @var Repetition[] */
    private $repetitions;
    /** @var @var SubProductSubscription[] */
    private $items;
    /** @var @var string */
    private $createdAt;
    /** @var @var string */
    private $updatedAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ProductSubscription
     */
    public function setId($id)
    {
        $this->id = intval($id);
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
     * @return ProductSubscription
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @param string $creditCard true or false
     */
    public function setCreditCard($creditCard)
    {
        if ($creditCard != '1' && $creditCard != '0') {
            throw new InvalidParamException(
                "Credit card should be 1 or 0!",
                $creditCard
            );
        }
        $this->creditCard = $creditCard;
    }

    /**
     * @return string true or false
     */
    public function getBoleto()
    {
        return $this->boleto;
    }

    /**
     * @param string $boleto 1 or 0
     */
    public function setBoleto($boleto)
    {
        if ($boleto != '1' && $boleto != '0') {
            throw new InvalidParamException(
                "Boleto should be 1 or 0",
                $boleto
            );
        }
        $this->boleto = $boleto;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        if (empty($status)) {
            throw new InvalidParamException(
                "Status should not be empty!",
                $status
            );
        }
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getBillingType()
    {
        return $this->billingType;
    }

    /**
     * @param string $billingType
     */
    public function setBillingType($billingType)
    {
        if (empty($billingType)) {
            throw new InvalidParamException(
                "Billing type should not be empty!",
                $billingType
            );
        }
        $this->billingType = $billingType;
    }

    /**
     * @return int
     */
    public function getAllowInstallments()
    {
        return $this->allowInstallments;
    }

    /**
     * @param string $allowInstallments 1 or 0
     * @throws InvalidParamException
     */
    public function setAllowInstallments($allowInstallments)
    {
        if ($allowInstallments != '1' && $allowInstallments != '0') {
            throw new InvalidParamException(
                "Allow installments should be 1 or 0!",
                $allowInstallments
            );
        }
        $this->allowInstallments = $allowInstallments;
    }

    /**
     * @return array
     */
    public function getRepetitions()
    {
        return $this->repetitions;
    }

    /**
     * @param Repetition $repetition
     * @return ProductSubscription
     */
    public function addRepetition(Repetition $repetition)
    {
        $this->repetitions[] = $repetition;
        return $this;
    }

    /**
     * @return SubProduct[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param SubProduct $items
     * @return ProductSubscription
     */
    public function addItems(SubProduct $items)
    {
        $this->items[] = $items;
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
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt->format(self::DATE_FORMAT);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt->format(self::DATE_FORMAT);
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
        $obj = new \stdClass();

        $obj->id = $this->getId();
        $obj->productId = $this->getProductId();
        $obj->creditCard = $this->getCreditCard();
        $obj->boleto = $this->getBoleto();
        $obj->status = $this->getStatus();
        $obj->billintType = $this->getBillingType();
        $obj->allowInstallments = $this->getAllowInstallments();
        $obj->repetitions = $this->getRepetitions();
        $obj->items = $this->getItems();
        $obj->createdAt = $this->getCreatedAt();
        $obj->updatedAt = $this->getUpdatedAt();

        return $obj;
    }
}