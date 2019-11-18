<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

class SubscriptionHandlerService extends AbstractHandlerService
{
    protected function handleCreated(Webhook $webhook)
    {
        throw new \Exception('Not implemented');
    }

    protected function handleCanceled(Webhook $webhook)
    {
        throw new \Exception('Not implemented canceled');
    }

    protected function loadOrder(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();
        /**
         *
         * @var Order $order
         */
        $order = $webhook->getEntity();
       // $order = $orderRepository->findByMundipaggId($order->getMundipaggId());
        $order = $orderRepository->findByCode($order->getCode());
        if ($order === null) {

            $orderDecoratorClass =
                MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

            /**
             *
             * @var Order $webhookOrder
             */
            $webhookOrder = $webhook->getEntity();
            /**
             *
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