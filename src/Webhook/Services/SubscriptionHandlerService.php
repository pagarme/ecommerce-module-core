<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\Repositories\SubscriptionRepository;

class SubscriptionHandlerService extends AbstractHandlerService
{
    protected function handleCreated(Webhook $webhook)
    {
        throw new \Exception('Not implemented');
    }

    protected function handleCanceled(Webhook $webhook)
    {
        $subscriptionRepository = new SubscriptionRepository();
        $orderService = new OrderService();
        $i18n = new LocalizationService();
        $orderFactory = new OrderFactory();

        /**
         * @var Subscription
         */
        $subscription = $webhook->getEntity();

        $this->order->setStatus($subscription->getStatus());

        $subscriptionRepository->save($this->order);

        $history = $i18n->getDashboard('Subscription canceled');
        $this->order->getPlatformOrder()->addHistoryComment($history);

        $platformOrderStatus = ucfirst(
            $this->order->getPlatformOrder()
                ->getPlatformOrder()
                ->getStatus()
        );

        $realOrder = $orderFactory->createFromSubscriptionData(
            $this->order,
            $platformOrderStatus
        );

        $orderService->syncPlatformWith($realOrder);

        $result = [
            "message" => 'Subscription cancel registered',
            "code" => 200
        ];

        return $result;
    }

    public function loadOrder(Webhook $webhook)
    {
        $subscriptionRepository = new SubscriptionRepository();
        $apiService = new ApiService();

        $subscriptionId = $webhook->getEntity()->getSubscriptionId()->getValue();
        $subscriptionObject = $apiService->getSubscription(new SubscriptionId($subscriptionId));

        if (!$subscriptionObject) {
            throw new Exception('Code not found.', 400);
        }

        $subscription = $subscriptionRepository->findByCode($subscriptionObject->getCode());
        if ($subscription === null) {
            $code = $subscriptionObject->getCode();
            throw new NotFoundException("Subscription #{$code} not found.");
        }

        $this->order = $subscription;
    }
}
