<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use MundiAPILib\Models\CreatePlanRequest;
use MundiAPILib\Models\UpdatePlanRequest;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Recurrence\Interfaces\RecurrenceEntityInterface;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Kernel\ValueObjects\NumericString;
use Mundipagg\Core\Recurrence\ValueObjects\PlanId;

final class Plan extends AbstractEntity implements RecurrenceEntityInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const RECURRENCE_TYPE = "plan";

    protected $id = null;
    private $interval;
    private $name;
    private $description;
    private $productId;
    private $creditCard;
    private $boleto;
    private $status;
    private $billingType;
    private $allowInstallments;
    private $createdAt;
    private $updatedAt;
    private $subProduct;
    private $items;
    private $trialPeriodDays;

    public function getRecurrenceType()
    {
        return self::RECURRENCE_TYPE;
    }

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
     */
    public function setName($name)
    {
        $this->name = $name;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
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
     */
    public function setProductId($productId)
    {
        if (!is_numeric($productId)) {
            throw new InvalidParamException(
                "Product id should be an integer!",
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
        return $this;
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
        return $this;
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
        return $this;
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
        return $this;
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
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt->format(self::DATE_FORMAT);
        return $this;
    }

    public function getIntervalType()
    {
        if ($this->getInterval() != null) {
            return $this->getInterval()->getIntervalType();
        }

        return null;
    }

    public function getIntervalCount()
    {
        if ($this->getInterval() != null) {
            return $this->getInterval()->getIntervalCount();
        }

        return null;
    }

    /**
     * @param array $items An array of Subproducts aggregate
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return SubProduct
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function getTrialPeriodDays()
    {
        return $this->trialPeriodDays;
    }

    /**
     * @param mixed $trialPeriodDays
     * @throws InvalidParamException
     */
    public function setTrialPeriodDays($trialPeriodDays)
    {
        if (!is_numeric($trialPeriodDays)) {
            throw new InvalidParamException(
                "Trial period days should be an integer!",
                $trialPeriodDays
            );
        }
        $this->trialPeriodDays = $trialPeriodDays;
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
        $obj->intervalType = $this->getIntervalType();
        $obj->intervalCount = $this->getIntervalCount();
        $obj->productId = $this->getProductId();
        $obj->creditCard = $this->getCreditCard();
        $obj->boleto = $this->getBoleto();
        $obj->status = $this->getStatus();
        $obj->billingType = $this->getBillingType();
        $obj->allowInstallments = $this->getAllowInstallments();
        $obj->createdAt = $this->getCreatedAt();
        $obj->updatedAt = $this->getUpdatedAt();
        $obj->trialPeriodDays = $this->getTrialPeriodDays();
        $obj->items = $this->getItems();

        return $obj;
    }

    public function convertToSdkRequest($update = false)
    {
        $planRequest = new CreatePlanRequest();
        if ($update) {
            $planRequest = new UpdatePlanRequest();
            $planRequest->status = $this->getStatus();
            $planRequest->currency = $this->getCurrency();
        }

        $planRequest->description = $this->getDescription();
        $planRequest->name = $this->getName();
        $planRequest->intervalCount = $this->getIntervalCount();
        $planRequest->interval = $this->getIntervalType();
        $planRequest->billingType = $this->getBillingType();

        if ($this->getCreditCard()) {
            $planRequest->paymentMethods[] = 'credit_card';
        }
        if ($this->getBoleto()) {
            $planRequest->paymentMethods[] = 'boleto';
        }

        //$planRequest->trialPeriodDays

        $items = $this->getItems();
        if ($items !== null) {
            foreach ($items as $item) {
                $itemsSdk[] = $item->convertToSDKRequest();
            }
            $planRequest->items = $itemsSdk;
        }

        return $planRequest;
    }

    public function getCurrency()
    {
        return 'BRL';
    }
}