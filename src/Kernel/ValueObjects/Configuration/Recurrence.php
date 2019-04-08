<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Configuration;

use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;

final class Recurrence extends AbstractValueObject
{
    /** @var bool */
    private $planSubscription;

    /** @var bool */
    private $singleSubscription;

    /** @var bool */
    private $paymentUpdateCustomer;

    /** @var bool */
    private $creditCardUpdateCustomer;

    /** @var bool */
    private $subscriptionInstallment;

    /** @var string */
    private $checkoutConflitMessage;

    public function __construct(
        $planSubscription,
        $singleSubscription,
        $paymentUpdateCustomer,
        $creditCardUpdateCustomer,
        $subscriptionInstallment,
        $checkoutConflitMessage
    ) {
        $this->setPlanSubscription($planSubscription);
        $this->setSingleSubscription($singleSubscription);
        $this->setPaymentUpdateCustomer($paymentUpdateCustomer);
        $this->setCreditCardUpdateCustomer($creditCardUpdateCustomer);
        $this->setSubscriptionInstallment($subscriptionInstallment);
        $this->setCheckoutConflitMessage($checkoutConflitMessage);
    }

    /**
     * @return bool
     */
    public function isPlanSubscriptionEnabled()
    {
        return $this->planSubscription;
    }

    /**
     * @param mixed $planSubscription
     */
    private function setPlanSubscription($planSubscription)
    {
        $this->planSubscription = $planSubscription;
    }

    /**
     * @return bool
     */
    public function isSingleSubscriptionEnabled()
    {
        return $this->singleSubscription;
    }

    /**
     * @param bool $singleSubscription
     */
    private function setSingleSubscription($singleSubscription)
    {
        $this->singleSubscription = $singleSubscription;
    }

    /**
     * @return bool
     */
    public function isPaymentUpdateCustomerEnabled()
    {
        return $this->paymentUpdateCustomer;
    }

    /**
     * @param bool $paymentUpdateCustomer
     */
    private function setPaymentUpdateCustomer($paymentUpdateCustomer)
    {
        $this->paymentUpdateCustomer = $paymentUpdateCustomer;
    }

    /**
     * @return bool
     */
    public function isCreditCardUpdateCustomerEnabled()
    {
        return $this->creditCardUpdateCustomer;
    }

    /**
     * @param bool $creditCardUpdateCustomer
     */
    private function setCreditCardUpdateCustomer($creditCardUpdateCustomer)
    {
        $this->creditCardUpdateCustomer = $creditCardUpdateCustomer;
    }

    /**
     * @return bool
     */
    public function isSubscriptionInstallmentEnabled()
    {
        return $this->subscriptionInstallment;
    }

    /**
     * @param bool $subscriptionInstallment
     */
    private function setSubscriptionInstallment($subscriptionInstallment)
    {
        $this->subscriptionInstallment = $subscriptionInstallment;
    }

    /**
     * @return string
     */
    public function getCheckoutConflitMessage()
    {
        return $this->checkoutConflitMessage;
    }

    /**
     * @param string $checkoutConflitMessage
     */
    private function setCheckoutConflitMessage($checkoutConflitMessage)
    {
        $this->checkoutConflitMessage = $checkoutConflitMessage;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->planSubscription === $object->isPlanSubscriptionEnabled() &&
            $this->singleSubscription === $object->isSingleSubscriptionEnabled() &&
            $this->paymentUpdateCustomer === $object->isPaymentUpdateCustomerEnabled() &&
            $this->creditCardUpdateCustomer === $object->isCreditCardUpdateCustomerEnabled() &&
            $this->subscriptionInstallment === $object->isSubscriptionInstallmentEnabled() &&
            $this->checkoutConflitMessage === $object->getCheckoutConflitMessage();
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

        $obj->planSubscription = $this->isPlanSubscriptionEnabled();
        $obj->singleSubscription = $this->isSingleSubscriptionEnabled();
        $obj->paymentUpdateCustomer = $this->isPaymentUpdateCustomerEnabled();
        $obj->creditCardUpdateCustomer = $this->isCreditCardUpdateCustomerEnabled();
        $obj->subscriptionInstallment = $this->isSubscriptionInstallmentEnabled();
        $obj->checkoutConflitMessage = $this->getCheckoutConflitMessage();

        return $obj;
    }
}