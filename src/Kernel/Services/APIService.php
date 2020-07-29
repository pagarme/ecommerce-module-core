<?php

namespace Mundipagg\Core\Kernel\Services;

use MundiAPILib\APIException;
use MundiAPILib\Exceptions\ErrorException;
use MundiAPILib\Models\CreateCancelChargeRequest;
use MundiAPILib\Models\CreateCaptureChargeRequest;
use MundiAPILib\Models\CreateChargeRequest;
use MundiAPILib\Models\CreateInvoiceRequest;
use MundiAPILib\Models\ListInvoicesResponse;
use MundiAPILib\MundiAPIClient;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\InvoiceId;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\ValueObjects\Key\PublicKey;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Order;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\Factories\SubscriptionFactory;

class APIService
{
    private $apiClient;
    private $logService;

    public function __construct()
    {
        $this->apiClient = $this->getMundiPaggApiClient();
        $this->logService = new OrderLogService(2);
    }

    public function getCharge(ChargeId $chargeId)
    {
        try {
            $chargeController = $this->getChargeController();

            $this->logService->orderInfo(
                $chargeId,
                'Get charge from api'
            );

            $response = $chargeController->getCharge($chargeId->getValue());

            $this->logService->orderInfo(
                $chargeId,
                'Get charge response: ',
                $response
            );

            return json_decode(json_encode($response), true);

        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public function cancelCharge(Charge &$charge, $amount = 0)
    {
        try {
            $chargeId = $charge->getMundipaggId()->getValue();
            $request = new CreateCancelChargeRequest();
            $request->amount = $amount;

            if (empty($amount)) {
                $request->amount = $charge->getAmount();
            }

            $chargeController = $this->getChargeController();
            $result = $chargeController->cancelCharge($chargeId, $request);
            $charge->cancel($amount);

            return null;
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public function captureCharge(Charge &$charge, $amount = 0)
    {
        try {
            $chargeId = $charge->getMundipaggId()->getValue();
            $request = new CreateCaptureChargeRequest;
            $request->amount = $amount;

            $chargeController = $this->getChargeController();
            $result = $chargeController->captureCharge($chargeId, $request);

            return $result;
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public function createOrder(Order $order)
    {
        $endpoint = $this->getAPIBaseEndpoint();

        $orderRequest = $order->convertToSDKRequest();
        $orderRequest->metadata = $this->getRequestMetaData();
        $publicKey = MPSetup::getModuleConfiguration()->getPublicKey()->getValue();

        $message =
            'Create order Request from ' .
            $publicKey .
            ' to ' .
            $endpoint;

        $this->logService->orderInfo(
            $order->getCode(),
            $message,
            $orderRequest
        );

        $orderController = $this->getOrderController();

        try {
            $response = $orderController->createOrder($orderRequest);
            $this->logService->orderInfo(
                $order->getCode(),
                'Create order Response',
                $response
            );

            return json_decode(json_encode($response), true);

        } catch (ErrorException $e) {
            $this->logService->exception($e);
            return [
                "message" => $e->getMessage()
            ];
        }
    }

    private function getRequestMetaData()
    {
        $versionService = new VersionService();
        $metadata = new \stdClass();

        $metadata->moduleVersion = $versionService->getModuleVersion();
        $metadata->coreVersion = $versionService->getCoreVersion();
        $metadata->platformVersion = $versionService->getPlatformVersion();

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

    /**
     *
     * @return \MundiAPILib\Controllers\CustomersController
     */
    private function getCustomerController()
    {
        return $this->apiClient->getCustomers();
    }

    private function getMundiPaggApiClient()
    {
        $config = MPSetup::getModuleConfiguration();

        $secretKey = null;
        if ($config->getSecretKey() != null) {
            $secretKey = $config->getSecretKey()->getValue();
        }
        $password = '';

        \MundiAPILib\Configuration::$basicAuthPassword = '';

        return new MundiAPIClient($secretKey, $password);
    }

    private function getAPIBaseEndpoint()
    {
        return \MundiAPILib\Configuration::$BASEURI;
    }

    public function updateCustomer(Customer $customer)
    {
        return $this->getCustomerController()->updateCustomer(
            $customer->getMundipaggId()->getValue(),
            $customer->convertToSDKRequest()
        );
    }

    private function getSubscriptionController()
    {
        return $this->apiClient->getSubscriptions();
    }

    private function getInvoiceController()
    {
        return $this->apiClient->getInvoices();
    }

    public function createSubscription(Subscription $subscription)
    {
        $endpoint = $this->getAPIBaseEndpoint();

        $subscriptionRequest = $subscription->convertToSDKRequest();
        $subscriptionRequest->metadata = $this->getRequestMetaData();
        $publicKey = MPSetup::getModuleConfiguration()->getPublicKey()->getValue();

        $message =
            'Create subscription request from ' .
            $publicKey .
            ' to ' .
            $endpoint;

        $this->logService->orderInfo(
            $subscription->getCode(),
            $message,
            $subscriptionRequest
        );

       $subscriptionController = $this->getSubscriptionController();

        try {
            $response = $subscriptionController->createSubscription($subscriptionRequest);
            $this->logService->orderInfo(
                $subscription->getCode(),
                'Create subscription response',
                $response
            );

            return json_decode(json_encode($response), true);

        } catch (ErrorException $e) {
            $this->logService->exception($e);
            return null;
        }
    }

    public function getSubscription(SubscriptionId $subscriptionId)
    {
        try {
            $subscriptionController = $this->getSubscriptionController();

            $subscriptionData = $subscriptionController->getSubscription(
                $subscriptionId->getValue()
            );

            $subscriptionData = json_decode(json_encode($subscriptionData), true);

            $subscriptionData['interval_type'] = $subscriptionData['interval'];

            $subscriptionFactory = new SubscriptionFactory();
            return $subscriptionFactory->createFromPostData($subscriptionData);
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get subscription invoice
     * @param SubscriptionId $subscriptionId
     * @return ListInvoicesResponse
     */
    public function getSubscriptionInvoice(SubscriptionId $subscriptionId)
    {
        try {
            $invoiceController = $this->getInvoiceController();

            $this->logService->orderInfo(
                $subscriptionId,
                'Get invoice from subscription.'
            );

            $response = $invoiceController->getInvoices(
                1,
                1,
                null,
                null,
                $subscriptionId->getValue()
            );

            $this->logService->orderInfo(
                $subscriptionId,
                'Invoice response: ',
                $response
            );

            return json_decode(json_encode($response), true);;

        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $endpoint = $this->getAPIBaseEndpoint();

        $publicKey = MPSetup::getModuleConfiguration()->getPublicKey()->getValue();

        $message =
            'Cancel subscription request from ' .
            $publicKey .
            ' to ' .
            $endpoint;

        $this->logService->orderInfo(
            $subscription->getCode(),
            $message
        );

        $subscriptionController = $this->getSubscriptionController();

        try {
            $response = $subscriptionController->cancelSubscription(
                $subscription->getMundipaggId()
            );
            $this->logService->orderInfo(
                $subscription->getCode(),
                'Cancel subscription response',
                $response
            );

            return json_decode(json_encode($response), true);

        } catch (\Exception $e) {
            $this->logService->exception($e);
            return $e;
        }
    }

    public function cancelInvoice(Invoice &$invoice, $amount = 0)
    {
        try {
            $invoiceId = $invoice->getMundipaggId()->getValue();
            $request = new CreateInvoiceRequest();

            $invoiceController =  $this->apiClient->getInvoices();
            return $invoiceController->cancelInvoice($invoiceId);

        } catch (APIException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}