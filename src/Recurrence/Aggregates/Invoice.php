<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod;

class Invoice extends AbstractEntity
{
    private $amount;
    private $status;
    private $paymentMethod;
    private $charge;
    private $installments;
    private $totalDiscount;
    private $totalIncrement;
    private $customer;
    private $cycle;

    /**
     * @var SubscriptionId
     */
    private $subscriptionId;

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
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return mixed
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * @param mixed $charge
     */
    public function setCharge(Charge $charge)
    {
        $this->charge = $charge;
    }

    /**
     * @return mixed
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @param mixed $installments
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;
    }

    /**
     * @return mixed
     */
    public function getTotalDiscount()
    {
        return $this->totalDiscount;
    }

    /**
     * @param mixed $totalDiscount
     */
    public function setTotalDiscount($totalDiscount)
    {
        $this->totalDiscount = $totalDiscount;
    }

    /**
     * @return mixed
     */
    public function getTotalIncrement()
    {
        return $this->totalIncrement;
    }

    /**
     * @param mixed $totalIncrement
     */
    public function setTotalIncrement($totalIncrement)
    {
        $this->totalIncrement = $totalIncrement;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     *
     * @return Cycle
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     *
     * @param Cycle $cycle
     * @return Subscription
     */
    public function setCycle(Cycle $cycle)
    {
        $this->cycle = $cycle;
        return $this;
    }

    public function getCycleStart()
    {
        if (!empty($this->getCycle())) {
            return $this->getCycle()->getCycleStart();
        }

        return null;
    }

    public function getCycleEnd()
    {
        if (!empty($this->getCycle())) {
            return $this->getCycle()->getCycleEnd();
        }

        return null;
    }

    public function jsonSerialize()
    {
        return [
            'subscriptionId' => $this->getSubscriptionId()
        ];
    }
}
