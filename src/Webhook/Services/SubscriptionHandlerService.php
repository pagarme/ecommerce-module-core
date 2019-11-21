<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\OrderService;
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

        /**
         * @var Subscription
         */
        $subscription = $webhook->getEntity();

        $outdatedSubscription = $subscriptionRepository->findByMundipaggId(
            $subscription->getMundipaggId()
        );

        if ($outdatedSubscription != null) {
            $outdatedSubscription->setStatus($subscription->getStatus());
            $subscription = $outdatedSubscription;
        }

        $subscriptionRepository->save($subscription);

        $history = $i18n->getDashboard('Subscription canceled');
        $this->order->getPlatformOrder()->addHistoryComment($history);

        $orderService->syncPlatformWith($this->order);

        $result = [
            "message" => 'Subscription cancel registered',
            "code" => 200
        ];

        return $result;
    }

    protected function loadOrder(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();

        /**
         * @var Order $order
         */
        $order = $webhook->getEntity();
        $order = $orderRepository->findByCode($order->getCode());
        if ($order === null) {
            $orderDecoratorClass = MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

            /**
             * @var Order $webhookOrder
             */
            $webhookOrder = $webhook->getEntity();

            /**
             * @var PlatformOrderInterface $order
             */
            $order = new $orderDecoratorClass();
            $order->loadByIncrementId($webhookOrder->getCode());

            if ($order->getIncrementId() === null) {
                throw new NotFoundException(
                    "Order Not found!"
                );
            }

            $orderFactory = new OrderFactory();
            $order = $orderFactory->createFromPlatformData(
                $order,
                $webhookOrder->getMundipaggId()->getValue()
            );
        }

        $this->order = $order;
    }
}
