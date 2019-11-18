<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Payment\Factories\CustomerFactory;
use Mundipagg\Core\Kernel\Aggregates\Subscription;
use Mundipagg\Core\Kernel\ValueObjects\SubscriptionStatus;
use Mundipagg\Core\Recurrence\Factories\CycleFactory;
use Throwable;

class SubscriptionFactory implements FactoryInterface
{
    /**
     *
     * @param  array $postData
     * @return \Mundipagg\Core\Kernel\Abstractions\AbstractEntity|Subscription
     * @throws NotFoundException
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $subscription = new Subscription();
        $status = $postData['status'];

        $subscription->setMundipaggId(new SubscriptionId($postData['id']));

        try {
            SubscriptionStatus::$status();
        }catch(Throwable $e) {
            throw new InvalidParamException(
                "Invalid subscription status!",
                $status
            );
        }

        $subscription->setStatus(SubscriptionStatus::$status());
        $subscription->setPlatformOrder($this->getPlatformOrder($postData['code']));

        if(!empty($postData['current_cycle'])) {
            $cycleFactory = new CycleFactory();
            $cycle = $cycleFactory->createFromPostData($postData['current_cycle']);
            $subscription->setCycle($cycle);
        }

        return $subscription;
    }

    private function getPlatformOrder($code)
    {
        $orderDecoratorClass =
            MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

        /**
         *
         * @var PlatformOrderInterface $order
         */
        $order = new $orderDecoratorClass();
        $order->loadByIncrementId($code);
        return $order;
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {

    }
}