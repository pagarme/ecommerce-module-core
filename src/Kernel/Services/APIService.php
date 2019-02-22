<?php

namespace Mundipagg\Core\Kernel\Services;

use MundiAPILib\APIException;
use MundiAPILib\Models\CreateCancelChargeRequest;
use MundiAPILib\MundiAPIClient;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Payment\Aggregates\Order;

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