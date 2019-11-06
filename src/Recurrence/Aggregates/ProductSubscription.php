<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

class ProductSubscription extends AbstractEntity
{
    const PREPAID = 'prepaid';

    /** @var int */
    protected $id;
    /** @var int */
    protected $productId;
    /** @var bool */
    protected $isEnabled;
    /** @var boolean */
    protected $acceptCreditCard = false;
    /** @var boolean */
    protected $acceptBoleto = false;
    /** @var boolean */
    protected $allowInstallments = false;
    /** @var Repetition[] */
    protected $repetitions;
    /** @var string */
    protected $billingType = self::PREPAID;
    /** @var @var SubProduct[] */
    protected $items;
    /** @var @var string */
    protected $createdAt;
    /** @var @var string */
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
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     * @return ProductSubscription
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = boolval($isEnabled);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAcceptCreditCard()
    {
        return $this->acceptCreditCard;
    }

    /**
     * @param bool $acceptCreditCard
     * @return Template
     */
    public function setAcceptCreditCard($acceptCreditCard)
    {
        $this->acceptCreditCard = boolval(intval($acceptCreditCard));
        return $this;
    }

    /**
     * @return bool
     */
    public function isAcceptBoleto()
    {
        return $this->acceptBoleto;
    }

    /**
     * @param bool $acceptBoleto
     * @return Template
     */
    public function setAcceptBoleto($acceptBoleto)
    {
        $this->acceptBoleto = boolval(intval($acceptBoleto));
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowInstallments()
    {
        return $this->allowInstallments;
    }

    /**
     * @param bool $allowInstallments
     * @return Template
     */
    public function setAllowInstallments($allowInstallments)
    {
        $this->allowInstallments = boolval(intval($allowInstallments));
        return $this;
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
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $repetitions = [];
        foreach ($this->repetitions as $repetition) {
            $repetitions[] = [
                "discountType" => $repetition->getDiscountType(),
                "discountValue" => $repetition->getDiscountValue(),
                "intervalCount" => $repetition->getintervalCount(),
                "intervalType" => $repetition->getIntervalType()
            ];
        }

        return [
            "id" => $this->getId(),
            "isEnabled" => $this->isEnabled(),
            "acceptBoleto" => $this->isAcceptBoleto(),
            "acceptCreditCard" => $this->isAcceptCreditCard(),
            "allowInstallments" => $this->isAllowInstallments(),
            "trial" => $this->getTrial(),
            "repetitions" => $repetitions,
        ];
    }
}