<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Kernel\ValueObjects\NumericString;

final class Plan extends AbstractEntity
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    protected $id;
    private $interval;
    private $planId;
    private $productId;
    private $creditCard;
    private $boleto;
    private $status;
    private $billingType;
    private $allowInstallments;
    private $createdAt;
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
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return IntervalValueObject
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param IntervalValueObject $interval
     */
    public function setInterval(IntervalValueObject $interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return string
     */
    public function getPlanId()
    {
        return $this->planId;
    }

    /**
     * @param string $planId
     */
    public function setPlanId($planId)
    {
        $this->planId = $planId;
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
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return boolean
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
     * @return boolean
     */
    public function getBoleto()
    {
        return $this->boleto;
    }

    /**
     * @param boolean $boleto
     */
    public function setBoleto($boleto)
    {
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
     * @param mixed $status
     */
    public function setStatus($status)
    {
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
        $this->billingType = $billingType;
    }

    /**
     * @return boolean
     */
    public function getAllowInstallments()
    {
        return $this->allowInstallments;
    }

    /**
     * @param boolean $allowInstallments
     */
    public function setAllowInstallments($allowInstallments)
    {
        $this->allowInstallments = $allowInstallments;
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

    protected function getIntervalType()
    {
        if ($this->getInterval() != null) {
            return $this->getInterval()->getIntervalType();
        }

        return null;
    }

    protected function getIntervalCount()
    {
        if ($this->getInterval() != null) {
            return $this->getInterval()->getIntervalCount();
        }

        return null;
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
        $obj->planId = $this->getPlanId();
        $obj->intervalType = $this->getIntervalType();
        $obj->intervalCount = $this->getIntervalCount();
        $obj->productId = $this->getProductId();
        $obj->creditCard = $this->getCreditCard();
        $obj->boleto = $this->getBoleto();
        $obj->status = $this->getStatus();
        $obj->billintType = $this->getBillingType();
        $obj->allowInstallments = $this->getAllowInstallments();
        $obj->createdAt = $this->getCreatedAt();
        $obj->updatedAt = $this->getUpdatedAt();

        return $obj;
    }
}