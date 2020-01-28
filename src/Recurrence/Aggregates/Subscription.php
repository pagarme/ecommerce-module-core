<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use MundiAPILib\Models\CreateSubscriptionRequest;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Interfaces\ChargeInterface;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Payment\Aggregates\Shipping;
use Mundipagg\Core\Payment\Traits\WithCustomerTrait;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod;
use Mundipagg\Core\Recurrence\ValueObjects\Id\PlanId;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;

class Subscription extends AbstractEntity
{
    use WithCustomerTrait;

    const RECURRENCE_TYPE = "subscription";

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

    private $intervalType;

    private $intervalCount;

    private $description;

    /**
     * @var PlanId
     */
    private $planId;

    /**
     * @var Order
     */
    private $platformOrder;
    private $items = [];
    private $billingType;
    private $cardToken;
    private $boletoDays;
    private $cardId;
    private $shipping;
    private $invoice;
    private $statementDescriptor;

    /**
     * @var Charge[]
     */
    private $charges;

    /**
     * @var Charge
     */
    private $currentCharge;
    private $increment;

    private $currentCycle;

    /**
     * @return mixed
     */
    public function getBillingType()
    {
        return $this->billingType;
    }

    /**
     * @param mixed $billingType
     */
    public function setBillingType($billingType)
    {
        $this->billingType = $billingType;
        return $this;
    }

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
     * @param SubscriptionStatus $status
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
        $this->paymentMethod = $paymentMethod->getPaymentMethod();
        return $this;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function getRecurrenceType()
    {
        return self::RECURRENCE_TYPE;
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

    /**
     *
     * @return Order
     */
    public function getPlatformOrder()
    {
        return $this->platformOrder;
    }

    /**
     *
     * @param PlatformOrderInterface $platformOrder
     * @return Subscription
     */
    public function setPlatformOrder(PlatformOrderInterface $platformOrder)
    {
        $this->platformOrder = $platformOrder;
        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return mixed
     */
    public function getCardToken()
    {
        return $this->cardToken;
    }

    /**
     * @param mixed $cardToken
     */
    public function setCardToken($cardToken)
    {
        $this->cardToken = $cardToken;
    }

    /**
     * @return mixed
     */
    public function getBoletoDays()
    {
        return $this->boletoDays;
    }

    /**
     * @param mixed $boletoDays
     */
    public function setBoletoDays($boletoDays)
    {
        $this->boletoDays = $boletoDays;
    }

    /**
     * @return mixed
     */
    public function getCardId()
    {
        return $this->cardId;
    }

    /**
     * @param mixed $cardId
     */
    public function setCardId($cardId)
    {
        $this->cardId = $cardId;
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
    }

    /**
     * @return Shipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping $shipping
     */
    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $invoice
     */
    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return Charge[]
     */
    public function getCharges()
    {
        if (!is_array($this->charges)) {
            return [];
        }
        return $this->charges;
    }

    /**
     *
     * @param  ChargeInterface $newCharge
     * @return Subscription
     */
    public function addCharge(ChargeInterface $newCharge)
    {
        $charges = $this->getCharges();
        //cant add a charge that was already added.
        foreach ($charges as $charge) {
            if ($charge->getMundipaggId()->equals(
                $newCharge->getMundipaggId()
            )
            ) {
                return $this;
            }
        }

        $charges[] = $newCharge;
        $this->charges = $charges;

        return $this;
    }

    /**
     * @param ChargeInterface[] $charges
     */
    public function setCharges($charges)
    {
        $this->charges = $charges;
    }

    /**
     * @return Increment
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * @param Increment $increment
     */
    public function setIncrement(Increment $increment)
    {
        $this->increment = $increment;
    }

    /**
     * @return string
     */
    public function getStatementDescriptor()
    {
        return $this->statementDescriptor;
    }

    /**
     * @param string $statementDescriptor
     */
    public function setStatementDescriptor($statementDescriptor)
    {
        $this->statementDescriptor = $statementDescriptor;
    }

    public function convertToSdkRequest()
    {
        $subscriptionRequest = new CreateSubscriptionRequest();

        $subscriptionRequest->code = $this->getCode();
        $subscriptionRequest->customer = $this->getCustomer()->convertToSDKRequest();
        $subscriptionRequest->billingType = $this->getBillingType();
        $subscriptionRequest->interval = $this->getIntervalType();
        $subscriptionRequest->intervalCount = $this->getIntervalCount();
        $subscriptionRequest->cardToken = $this->getCardToken();
        $subscriptionRequest->cardId = $this->getCardId();
        $subscriptionRequest->installments = $this->getInstallments();
        $subscriptionRequest->boletoDueDays = $this->getBoletoDays();
        $subscriptionRequest->paymentMethod = $this->getPaymentMethod();
        $subscriptionRequest->description = $this->getDescription();
        $subscriptionRequest->shipping = $this->getShipping()->convertToSDKRequest();
        $subscriptionRequest->statementDescriptor = $this->getStatementDescriptor();

        $subscriptionRequest->items = [];
        foreach ($this->getItems() as $item) {
            $subscriptionRequest->items[] = $item->convertToSDKRequest();
        }

        return $subscriptionRequest;
    }

    public function getStatusValue()
    {
        if ($this->getStatus() !== null) {
            return $this->getStatus()->getStatus();
        }
        return null;
    }

    public function getPlanIdValue()
    {
        if ($this->getPlanId() !== null) {
            return $this->getPlanId()->getValue();
        }
        return null;
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "subscriptionId" => $this->getMundipaggId(),
            "code" => $this->getCode(),
            "status" => $this->getStatusValue(),
            "paymentMethod" => $this->getPaymentMethod(),
            "planId" => $this->getPlanIdValue(),
            "intervalType" => $this->getIntervalType(),
            "intervalCount" => $this->getIntervalCount(),
            "installments" => $this->getInstallments(),
            "billingType" => $this->getBillingType()
        ];
    }

    /**
     * @return mixed
     */
    public function getCurrentCycle()
    {
        return $this->currentCycle;
    }

    /**
     * @param mixed $currentCycle
     */
    public function setCurrentCycle(Cycle $currentCycle)
    {
        $this->currentCycle = $currentCycle;
    }

    /**
     * @return ChargeInterface
     */
    public function getCurrentCharge()
    {
        return $this->currentCharge;
    }

    /**
     * @param ChargeInterface
     */
    public function setCurrentCharge(ChargeInterface $currentCharge)
    {
        $this->currentCharge = $currentCharge;
    }
}
