<?php

namespace Mundipagg\Core\Kernel\Services;

use MundiAPILib\APIException;
use MundiAPILib\Exceptions\ErrorException;
use MundiAPILib\Models\CreateCancelChargeRequest;
use MundiAPILib\Models\CreateCaptureChargeRequest;
use MundiAPILib\MundiAPIClient;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Order;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;

class APIService
{
    private $apiClient;
    private $logService;

    public function __construct()
    {
        $this->apiClient = $this->getMundiPaggApiClient();
        $this->logService = new OrderLogService(2);
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
        $orderRequest->metadata = $this->getOrderMetaData();
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
            return $e;
        }
    }

    private function getOrderMetaData()
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

        $secretKey = $config->getSecretKey()->getValue();
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

    public function getSubscription(SubscriptionId $subscriptionId)
    {
        try {
            $subscriptionController = $this->getSubscriptionController();
            $subscriptionData = $subscriptionController->getSubscription($subscriptionId->getValue());

            $subscriptionData = json_decode(json_encode($subscriptionData), true);

            $subscriptionFactory = new SubscriptionFactory();
            return $subscriptionFactory->createFromPostData($subscriptionData);
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    private function getSubscriptionController()
    {
        return $this->apiClient->getSubscriptions();
    }
}