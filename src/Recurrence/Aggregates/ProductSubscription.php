<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Mundipagg\Core\Recurrence\Interfaces\RepetitionInterface;

class ProductSubscription extends AbstractEntity implements ProductSubscriptionInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const RECURRENCE_TYPE = "subscription";

    /** @var int */
    protected $id = null;
    /** @var int */
    private $productId;
    /** @var int */
    private $cycles;
    /** @var boolean */
    private $creditCard = false;
    /** @var boolean */
    private $boleto = false;
    /** @var boolean */
    private $allowInstallments = false;
    /** @var Repetition[] */
    private $repetitions = [];
    /** @var boolean */
    private $sellAsNormalProduct;
    /** @var string */
    private $billingType = 'PREPAID';
    /** @var string */
    private $createdAt;
    /** @var string */
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
     * @throws InvalidParamException
     */
    public function setProductId($productId)
    {
        if (empty($productId)) {
            throw new InvalidParamException(
                "Product id should not be empty!",
                $productId
            );
        }
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
     * @param bool $creditCard
     */
    public function setCreditCard($creditCard)
    {
        $this->creditCard = $creditCard;
    }

    /**
     * @return bool
     */
    public function getBoleto()
    {
        return $this->boleto;
    }

    /**
     * @param bool $boleto
     */
    public function setBoleto($boleto)
    {
        $this->boleto = $boleto;
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
     * @throws InvalidParamException
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
     * @param bool $allowInstallments
     */
    public function setAllowInstallments($allowInstallments)
    {
        $this->allowInstallments = $allowInstallments;
    }

    /**
     * @return \Mundipagg\Core\Recurrence\Interfaces\RepetitionInterface[]|null
     */
    public function getRepetitions()
    {
        return $this->repetitions;
    }

    /**
     * @param \Mundipagg\Core\Recurrence\Interfaces\RepetitionInterface[] $repetitions
     * @return ProductSubscriptionInterface
     */
    public function setRepetitions(array $repetitions)
    {
        $this->repetitions = $repetitions;
        return $this;
    }

    /**
     * @param RepetitionInterface $repetition
     * @return ProductSubscription
     */
    public function addRepetition(RepetitionInterface $repetition)
    {
        $this->repetitions[] = $repetition;
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
        $obj->sellAsNormalProduct = $this->getSellAsNormalProduct();
        $obj->billintType = $this->getBillingType();
        $obj->allowInstallments = $this->getAllowInstallments();
        $obj->repetitions = $this->getRepetitions();
        $obj->cycles = $this->getCycles();
        $obj->createdAt = $this->getCreatedAt();
        $obj->updatedAt = $this->getUpdatedAt();

        return $obj;
    }

    public function getRecurrenceType()
    {
        return self::RECURRENCE_TYPE;
    }

    /**
     * @return int
     */
    public function getSellAsNormalProduct()
    {
        return $this->sellAsNormalProduct;
    }

    /**
     * @param bool $sellAsNormalProduct
     * @return ProductSubscription
     */
    public function setSellAsNormalProduct($sellAsNormalProduct)
    {
        $this->sellAsNormalProduct = $sellAsNormalProduct;
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
     * @return ProductSubscriptionInterface
     */
    public function setCycles($cycles)
    {
        $this->cycles = $cycles;
        return $this;
    }
}