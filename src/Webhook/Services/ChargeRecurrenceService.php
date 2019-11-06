<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractDataService;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\InvoiceService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\OrderLogService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Kernel\ValueObjects\InvoiceState;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;

class ChargeRecurrenceService
{
    /**
     * @var OrderLogService
     */
    private $logService;

    /**
     * @var APIService
     */
    private $apiService;

    /**
     * RecurrenceStrategyService constructor.
     */
    public function __construct()
    {
        $this->logService = new OrderLogService();
        $this->apiService = new ApiService();
    }

    public function handle(Order $order, Webhook $webhook)
    {
        var_dump($webhook->getEntity());
        $this->apiService->getSubscription(new SubscriptionId($webhook->getEntity()));


        $webhookStatus = ucfirst($webhook->getEntity()->getStatus()->getStatus());
        $statusHandler = 'handleOrderStatus' . $webhookStatus;

        $this->logService->orderInfo(
            $order->getCode(),
            "Handling recurrence webhook status: {$webhookStatus}"
        );

        return $this->$statusHandler($order);
    }

    private function handleOrderStatusPaid(Order $order)
    {
        $this->completePayment($order);
    }

    private function completePayment(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();

        //$this->createCaptureTransaction($order);

        $order->setStatus(OrderStatus::processing());
        $platformOrder->setState(OrderState::processing());

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Order paid.') .
            ' MundipaggId: ' . $order->getMundipaggId()->getValue()
        );

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);
    }

    private function createCaptureTransaction(Order $order)
    {
        $dataServiceClass = MPSetup::get(MPSetup::CONCRETE_DATA_SERVICE);

        $this->logService->orderInfo(
            $order->getCode(),
            "Creating Capture Transaction..."
        );

        /**
         * @var AbstractDataService $dataService
         */
        $dataService = new $dataServiceClass();
        $dataService->createCaptureTransaction($order);

        $this->logService->orderInfo(
            $order->getCode(),
            "Capture Transaction created."
        );
    }
}