<?php

namespace Mundipagg\Core\Payment\Aggregates;

use MundiAPILib\Models\CreateOrderRequest;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Payment\Aggregates\Payments\AbstractPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\SavedCreditCardPayment;
use Mundipagg\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use Mundipagg\Core\Payment\Traits\WithAmountTrait;
use Mundipagg\Core\Payment\Traits\WithCustomerTrait;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod as PaymentMethod;

final class Order extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;
    use WithCustomerTrait;

    private $paymentMethods = [
        'SavedCreditCardPayment' => 'returnCreditCardPaymentMethod',
        'BoletoPayment' => 'returnBoletoPaymentMethod',
        'NewCreditCardPayment' => 'returnCreditCardPaymentMethod'
    ];

    private $paymentMethod;

    /** @var string */
    private $code;
    /** @var Item[] */
    private $items;
    /** @var null|Shipping */
    private $shipping;
    /** @var AbstractPayment[] */
    private $payments;
    /** @var boolean */
    private $closed;

    /** @var boolean */
    private $antifraudEnabled;

    public function __construct()
    {
        $this->payments = [];
        $this->items = [];
        $this->closed = true;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = substr($code, 0, 52);
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }

    /**
     * @return Shipping|null
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping|null $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod($payment)
    {
        $paymentMethodClass = $this->discoverPaymentMethod($payment);

        if (isset($this->paymentMethods[$paymentMethodClass])) {
            $methodName = $this->paymentMethods[$paymentMethodClass];

            $this->paymentMethod = $this->$methodName();
        }
        return $this;
    }

    /**
     * @return AbstractPayment[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    public function addPayment(AbstractPayment $payment)
    {
        $this->validatePaymentInvariants($payment);
        $this->blockOverPaymentAttempt($payment);
        $this->setCaptureFlag($payment);
        $this->setPaymentMethod($payment);

        $payment->setOrder($this);

        if ($payment->getCustomer() === null) {
            $payment->setCustomer($this->getCustomer());
        }

        $this->payments[] = $payment;
    }

    /**
     * @return bool
     */
    public function isPaymentSumCorrect()
    {
        if (
            $this->amount === null ||
            empty($this->payments)
        ) {
            return false;
        }

        $sum = 0;
        foreach ($this->payments as $payment)
        {
            $sum += $payment->getAmount();
        }

        return $this->amount === $sum;
    }

    /**
     *  Blocks any overpayment attempt.
     *
     * @param AbstractPayment $payment
     * @throws \Exception
     */
    private function blockOverPaymentAttempt(AbstractPayment $payment)
    {
        $currentAmount = $payment->getAmount();
        foreach ($this->payments as $currentPayment) {
            $currentAmount += $currentPayment->getAmount();
        }

        if ($currentAmount > $this->amount) {
            throw new \Exception(
                'The sum of payment amounts is bigger than the amount of the order!',
                400
            );
        }
    }

    /**
     * Calls the invariant validator method of each payment method, if applicable.
     *
     * @param AbstractPayment $payment
     * @throws \Exception
     */
    private function validatePaymentInvariants(AbstractPayment $payment)
    {
        $paymentClass = $this->discoverPaymentMethod($payment);
        $paymentValidator = "validate$paymentClass";

        if (method_exists($this, $paymentValidator)) {
            $this->$paymentValidator($payment);
        }
    }

    private function discoverPaymentMethod(AbstractPayment $payment)
    {
        $paymentClass = get_class($payment);
        $paymentClass = explode ('\\', $paymentClass);
        $paymentClass = end($paymentClass);
        return $paymentClass;
    }

    private function validateSavedCreditCardPayment(SavedCreditCardPayment $payment)
    {
        if ($this->customer === null) {
            throw new \Exception(
                'To use a saved credit card payment in an order ' .
                'you must add a customer to it.',
                400
            );
        }

        $customerId = $this->customer->getMundipaggId();
        if ($customerId === null) {
            throw new \Exception(
                'You can\'t use a saved credit card of a fresh new customer',
                400
            );
        }

        if (!$customerId->equals($payment->getOwner())) {
            throw new \Exception(
                'The saved credit card informed doesn\'t belong to the informed customer.',
                400
            );
        }
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param bool $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * @return bool
     */
    public function isAntifraudEnabled()
    {
        $payments = $this->getPayments();

        foreach ($payments as $payment) {
            $payment;
        }

        $antifraudMinAmount = MPSetup::getModuleConfiguration()->getAntifraudMinAmount();

        if ($this->amount < $antifraudMinAmount) {
            return false;
        }
        return $this->antifraudEnabled;
    }

    /**
     * @param bool $antifraudEnabled
     */
    public function setAntifraudEnabled($antifraudEnabled)
    {
        $this->antifraudEnabled = $antifraudEnabled;
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

        $obj->customer = $this->getCustomer();
        $obj->code = $this->getCode();
        $obj->items = $this->getItems();

        $shipping = $this->getShipping();
        if ($shipping !== null) {
            $obj->shipping = $this->getShipping();
        }

        $obj->payments = $this->getPayments();
        $obj->closed = $this->isClosed();
        $obj->antifraudEnabled = $this->isAntifraudEnabled();

        return $obj;
    }

    /**
     * @return CreateOrderRequest
     */
    public function convertToSDKRequest()
    {
        $orderRequest = new CreateOrderRequest();

        $orderRequest->antifraudEnabled = $this->isAntifraudEnabled();
        $orderRequest->closed = $this->isClosed();
        $orderRequest->code = $this->getCode();
        $orderRequest->customer = $this->getCustomer()->convertToSDKRequest();

        $orderRequest->payments = [];
        foreach ($this->getPayments() as $payment) {
            $orderRequest->payments[] = $payment->convertToSDKRequest();
        }

        $orderRequest->items = [];
        foreach ($this->getItems() as $item) {
            $orderRequest->items[] = $item->convertToSDKRequest();
        }

        $shipping = $this->getShipping();
        if ($shipping !== null) {
            $orderRequest->shipping = $shipping->convertToSDKRequest();
        }

        return $orderRequest;
    }

    private function setCaptureFlag(AbstractPayment &$payment)
    {
        $capture = MPSetup::getModuleConfiguration()->isCapture();

        if (method_exists($payment, 'setCapture')) {
            $payment->setCapture($capture);
        }
    }

    private function returnCreditCardPaymentMethod()
    {
        return PaymentMethod::credit_card();
    }

    private function returnBoletoPaymentMethod()
    {
        return PaymentMethod::credit_card();
    }
}