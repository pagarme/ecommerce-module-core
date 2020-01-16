<?php

namespace Mundipagg\Core\Kernel\ValueObjects\Configuration;

use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;

final class RecurrenceConfig extends AbstractValueObject
{
    /** @var bool */
    private $enabled;

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
    private $checkoutConflictMessage;

    /** @var bool */
    private $showRecurrenceCurrencyWidget;

    public function __construct(
        $enabled = false,
        $checkoutConflictMessage = "",
        $showRecurrenceCurrencyWidget = false,
        $planSubscription = false,
        $singleSubscription = false,
        $paymentUpdateCustomer = false,
        $creditCardUpdateCustomer = false,
        $subscriptionInstallment = false
    ) {
        $this->setEnabled($enabled);
        $this->setPlanSubscriptionEnabled($planSubscription);
        $this->setSingleSubscriptionEnabled($singleSubscription);
        $this->setPaymentUpdateCustomerEnabled($paymentUpdateCustomer);
        $this->setCreditCardUpdateCustomerEnabled($creditCardUpdateCustomer);
        $this->setSubscriptionInstallmentEnabled($subscriptionInstallment);
        $this->setCheckoutConflictMessage($checkoutConflictMessage);
        $this->setShowRecurrenceCurrencyWidget($showRecurrenceCurrencyWidget);
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
     * @return RecurrenceConfig
     */
    private function setPlanSubscriptionEnabled($planSubscription)
    {
        $this->planSubscription = $planSubscription;
        return $this;
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
     * @return RecurrenceConfig
     */
    private function setSingleSubscriptionEnabled($singleSubscription)
    {
        $this->singleSubscription = $singleSubscription;
        return $this;
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
     * @return RecurrenceConfig
     */
    private function setPaymentUpdateCustomerEnabled($paymentUpdateCustomer)
    {
        $this->paymentUpdateCustomer = $paymentUpdateCustomer;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowRecurrenceCurrencyWidget()
    {
        return $this->showRecurrenceCurrencyWidget;
    }

    /**
     * @param bool $showRecurrenceCurrencyWidget
     * @return $this
     */
    private function setShowRecurrenceCurrencyWidget($showRecurrenceCurrencyWidget)
    {
        $this->showRecurrenceCurrencyWidget = $showRecurrenceCurrencyWidget;
        return $this;
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
     * @return RecurrenceConfig
     */
    private function setCreditCardUpdateCustomerEnabled($creditCardUpdateCustomer)
    {
        $this->creditCardUpdateCustomer = $creditCardUpdateCustomer;
        return $this;
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
     * @return RecurrenceConfig
     */
    private function setSubscriptionInstallmentEnabled($subscriptionInstallment)
    {
        $this->subscriptionInstallment = $subscriptionInstallment;
        return $this;
    }

    /**
     * @return string
     */
    public function getCheckoutConflictMessage()
    {
        return $this->checkoutConflictMessage;
    }

    /**
     * @param string $checkoutConflictMessage
     * @return RecurrenceConfig
     */
    private function setCheckoutConflictMessage($checkoutConflictMessage)
    {
        $this->checkoutConflictMessage = $checkoutConflictMessage;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return RecurrenceConfig
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
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
            $this->enabled === $object->isEnabled() &&
            $this->planSubscription === $object->isPlanSubscriptionEnabled() &&
            $this->singleSubscription === $object->isSingleSubscriptionEnabled() &&
            $this->paymentUpdateCustomer === $object->isPaymentUpdateCustomerEnabled() &&
            $this->creditCardUpdateCustomer === $object->isCreditCardUpdateCustomerEnabled() &&
            $this->subscriptionInstallment === $object->isSubscriptionInstallmentEnabled() &&
            $this->checkoutConflictMessage === $object->getCheckoutConflictMessage();
            $this->showRecurrenceCurrencyWidget === $object->isShowRecurrenceCurrencyWidget();
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

        $obj->enabled = $this->isEnabled();
        $obj->planSubscription = $this->isPlanSubscriptionEnabled();
        $obj->singleSubscription = $this->isSingleSubscriptionEnabled();
        $obj->paymentUpdateCustomer = $this->isPaymentUpdateCustomerEnabled();
        $obj->creditCardUpdateCustomer = $this->isCreditCardUpdateCustomerEnabled();
        $obj->subscriptionInstallment = $this->isSubscriptionInstallmentEnabled();
        $obj->checkoutConflictMessage = $this->getCheckoutConflictMessage();
        $obj->showRecurrenceCurrencyWidget = $this->isShowRecurrenceCurrencyWidget();

        return $obj;
    }
}