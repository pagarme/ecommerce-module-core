<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Payment\Traits\WithCustomerTrait;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod;
use Mundipagg\Core\Recurrence\ValueObjects\Id\PlanId;

class Subscription extends AbstractEntity
{
    use WithCustomerTrait;

    /**
     * @var SubscriptionId
     */
    private $subscriptionId;

    /**
     * @var string
     */
    private $code;

    /**
     * @var SubscriptionStatus
     */
    private $status;

    /**
     * @var int
     */
    private $installments;

    /**
     * @var PaymentMethod
     */
    private $paymentMethod;

    private $recurrenceType;

    private $intervalType;

    private $intervalCount;

    /**
     * @var PlanId
     */
    private $planId;

    /**
     * @return SubscriptionId
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @param  SubscriptionId $subscriptionId
     * @return $this
     */
    public function setSubscriptionId(SubscriptionId $subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param  string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @param  string $subscriptionStatus
     * @return $this
     */
    public function setStatus(SubscriptionStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    public function setInstallments($installments)
    {
        $this->installments = $installments;
        return $this;
    }

    public function getInstallments()
    {
        return $this->installments;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function setRecurrenceType($recurrenceType)
    {
        $this->recurrenceType = $recurrenceType;
        return $this;
    }

    public function getRecurrenceType()
    {
        return $this->recurrenceType;
    }

    public function setIntervalType($intervalType)
    {
        $this->intervalType = $intervalType;
        return $this;
    }

    public function getIntervalType()
    {
        return $this->intervalType;
    }

    public function setIntervalCount($intervalCount)
    {
        $this->intervalCount = $intervalCount;
        return $this;
    }

    public function getIntervalCount()
    {
        return $this->intervalCount;
    }

    public function setPlanId(PlanId $planId)
    {
        $this->planId = $planId;
        return $this;
    }

    public function getPlanId()
    {
        return $this->planId;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

}