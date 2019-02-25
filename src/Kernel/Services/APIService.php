<?php

namespace Mundipagg\Core\Kernel\Services;

use MundiAPILib\APIException;
use MundiAPILib\Models\CreateAddressRequest;
use MundiAPILib\Models\CreateBoletoPaymentRequest;
use MundiAPILib\Models\CreateCancelChargeRequest;
use MundiAPILib\Models\CreateCustomerRequest;
use MundiAPILib\Models\CreateOrderRequest;
use MundiAPILib\Models\CreatePaymentRequest;
use MundiAPILib\Models\CreatePhoneRequest;
use MundiAPILib\Models\CreatePhonesRequest;
use MundiAPILib\MundiAPIClient;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Order;
use Mundipagg\Core\Payment\Aggregates\Payments\AbstractCreditCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\AbstractPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\BoletoPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\NewCreditCardPayment;

class APIService
{
    private $apiClient;

    public function __construct()
    {
        $this->apiClient = $this->getMundiPaggApiClient();
    }

    public function cancelCharge(Charge &$charge)
    {
        try {
            $chargeId = $charge->getMundipaggId()->getValue();
            $request = new CreateCancelChargeRequest();

            $chargeController = $this->getChargeController();
            $result = $chargeController->cancelCharge($chargeId, $request);
            $charge->cancel();

            return null;
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public function createOrder(Order $order)
    {
        $orderRequest = new CreateOrderRequest();

        $orderRequest->antifraudEnabled = $order->isAntifraudEnabled();
        $orderRequest->closed = $order->isClosed();
        $orderRequest->code = $order->getCode();
        $orderRequest->metadata = $this->getOrderMetaData();
        $orderRequest->customer = $this->createCustomerRequest($order);

        $orderRequest->payments = $this->createPaymentRequests($order);

        $a = 1;

    }

    private function createPaymentRequests(Order $order)
    {
        $paymentRequests = [];
        $payments = $order->getPayments();

        foreach ($payments as $payment) {
            $newPayment = new CreatePaymentRequest();
            $newPayment->amount = $payment->getAmount();

            $baseMethod = (get_class($payment))::getBaseCode();
            $newPayment->$baseMethod = $this->preparePaymentData($payment);

            $paymentRequests[] = $newPayment;
        }

        return $paymentRequests;
    }

    private function preparePaymentData(AbstractPayment $payment)
    {
        $primitive = (get_class($payment))::getBaseCode();
        $paymentRequestClass = "MundiAPILib\Models\Create".ucfirst($primitive) . "PaymentRequest";
        $paymentRequest = new $paymentRequestClass();

        if (is_a($payment, AbstractCreditCardPayment::class)) {

            $identifier = 'cardId';
            if (is_a($payment, NewCreditCardPayment::class)) {
                $identifier = 'cardToken';
            }

            $paymentRequest->capture = $payment->isCapture();
            $paymentRequest->installments = $payment->getInstallments();
            $paymentRequest->$identifier = $payment->getIdentifier()->getValue();
            $paymentRequest->statementDescriptor =
                $payment->getStatementDescriptor();
        }

        /** @var BoletoPayment $payment */
        if (is_a($payment, BoletoPayment::class)) {
            /** @var CreateBoletoPaymentRequest $paymentRequest */
            $paymentRequest->bank = $payment->getBank()->getCode();
            $paymentRequest->instructions = $payment->getInstructions();
        }

        return $paymentRequest;
    }

    private function createCustomerRequest(Order $order)
    {
        $customerRequest = new CreateCustomerRequest();
        $customer = $order->getCustomer();

        $customerRequest->name = $customer->getName();
        $customerRequest->email = $customer->getEmail();
        $customerRequest->document = $customer->getDocument();
        $customerRequest->type = $customer->getType()->getType();
        $customerRequest->address = $this->createAddressRequest($customer);

        $customerRequest->phones = new CreatePhonesRequest();
        $customerRequest->phones->homePhone = new CreatePhoneRequest(
            $customer->getPhones()->getHome()->getCountryCode()->getValue(),
            $customer->getPhones()->getHome()->getNumber()->getValue(),
            $customer->getPhones()->getHome()->getAreaCode()->getValue()
        );
        $customerRequest->phones->mobilePhone = new CreatePhoneRequest(
            $customer->getPhones()->getMobile()->getCountryCode()->getValue(),
            $customer->getPhones()->getMobile()->getNumber()->getValue(),
            $customer->getPhones()->getMobile()->getAreaCode()->getValue()
        );

        return $customerRequest;
    }

    private function createAddressRequest(Customer $customer)
    {
        $addressRequest = new CreateAddressRequest();
        $address = $customer->getAddress();

        $addressRequest->city = $address->getCity();
        $addressRequest->complement = $address->getComplement();
        $addressRequest->country = $address->getCountry();
        $addressRequest->line1 = $address->getLine1();
        $addressRequest->line2 = $address->getLine2();
        $addressRequest->neighborhood = $address->getNeighborhood();
        $addressRequest->number = $address->getNumber();
        $addressRequest->state = $address->getState();
        $addressRequest->street = $address->getStreet();
        $addressRequest->zipCode = $address->getZipCode();

        return $addressRequest;
    }

    private function getOrderMetaData()
    {
        $versionService = new VersionService();
        $metadata = new \stdClass();

        $metadata->moduleVersion = $versionService->getModuleVersion();
        $metadata->coreVersion = $versionService->getCoreVersion();

        return $metadata;
    }

    public function getOrder(OrderId $orderId)
    {
        try {
            $orderController = $this->getOrderController();
            $orderData = $orderController->getOrder($orderId->getValue());

            $orderData = json_decode(json_encode($orderData), true);

            $orderFactory = new OrderFactory();
            return $orderFactory->createFromPostData($orderData);
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    private function getChargeController()
    {
        return $this->apiClient->getCharges();
    }

    /**
     *
     * @return \MundiAPILib\Controllers\OrdersController
     */
    private function getOrderController()
    {
        return $this->apiClient->getOrders();
    }

    private function getMundiPaggApiClient()
    {
        $config = MPSetup::getModuleConfiguration();

        $secretKey = $config->getSecretKey()->getValue();
        $password = '';

        return new MundiAPIClient($secretKey, $password);
    }
}