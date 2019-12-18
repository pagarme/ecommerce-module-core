<?php

namespace Mundipagg\Core\Recurrence\Interfaces;

use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Recurrence\ValueObjects\Id\PlanId;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus;

interface SubscriptionInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return SubscriptionInterface
     */
    public function setId($id);

    /**
     * @return Customer|null
     */
    public function getCustomer();

    /**
     * @param Customer|null $customer
     * @return SubscriptionInterface
     */
    public function setCustomer(Customer $customer);

    /**
     * @return int
     */
    public function getCode();

    /**
     * @param string $code
     * @return SubscriptionInterface
     */
    public function setCode($code);

    /**
     * @return Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus
     */
    public function getStatus();

    /**
     * @param Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus $status
     * @return SubscriptionInterface
     */
    public function setStatus(SubscriptionStatus $status);

    /**
     * @return bool
     */
    public function getInstallments();

    /**
     * @param bool $installments
     * @return SubscriptionInterface
     */
    public function setInstallments($installments);

    /**
     * @return Mundipagg\Core\Kernel\ValueObjects\PaymentMethod
     */
    public function getPaymentMethod();

    /**
     * @param Mundipagg\Core\Kernel\ValueObjects\PaymentMethod $paymentMethod
     * @return SubscriptionInterface
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod);

    /**
     * @return string
     */
    public function getRecurrenceType();

    /**
     * @return Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject
     */
    public function getInterval();

    /**
     * @param Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject $interval
     * @return SubscriptionInterface
     */
    public function setInterval(IntervalValueObject $intervalType);

    /**
     * @return Mundipagg\Core\Recurrence\ValueObjects\Id\PlanId
     */
    public function getPlanId();

    /**
     * @param Mundipagg\Core\Recurrence\ValueObjects\Id\PlanId $planId
     * @return SubscriptionInterface
     */
    public function setPlanId(PlanId $planId);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param mixed $createdAt
     * @return ProductSubscriptionInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * @param mixed $updatedAt
     * @return ProductSubscriptionInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt);
}