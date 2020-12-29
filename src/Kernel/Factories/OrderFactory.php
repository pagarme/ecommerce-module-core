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
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Payment\Factories\CustomerFactory;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Throwable;

class OrderFactory implements FactoryInterface
{
    /**
     *
     * @param array $postData
     * @return \Mundipagg\Core\Kernel\Abstractions\AbstractEntity|Order
     * @throws NotFoundException
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $order = new Order();
        $status = $postData['status'];

        $order->setMundipaggId(new OrderId($postData['id']));

        try {
            OrderStatus::$status();
        } catch (Throwable $e) {
            throw new InvalidParamException(
                "Invalid order status!",
                $status
            );
        }

        $order->setStatus(OrderStatus::$status());

        $order->setPlatformOrder(
            $this->getPlatformOrder($postData['code'])
        );

        $charges = $postData['charges'];

        $chargeFactory = new ChargeFactory();

        foreach ($charges as $charge) {
            $charge['order'] = [
                'id' => $order->getMundipaggId()->getValue()
            ];
            $newCharge = $chargeFactory->createFromPostData($charge);
            $order->addCharge($newCharge);
        }

        $customerFactory = new CustomerFactory();
        $customer = $customerFactory->createFromPostData($postData['customer']);
        $order->setCustomer($customer);

        return $order;
    }

    /**
     *
     * @param array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        $order = new Order;

        $order->setId($dbData['id']);
        $order->setMundipaggId(new OrderId($dbData['mundipagg_id']));

        $status = $dbData['status'];
        try {
            OrderStatus::$status();
        } catch (Throwable $e) {
            throw new InvalidParamException(
                "Invalid order status!",
                $status
            );
        }
        $order->setStatus(OrderStatus::$status());

        $chargeRepository = new ChargeRepository();
        $charges = $chargeRepository->findByOrderId($order->getMundipaggId());

        foreach ($charges as $charge) {
            $order->addCharge($charge);
        }

        $order->setPlatformOrder(
            $this->getPlatformOrder($dbData['code'])
        );

        return $order;
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
     * @param PlatformOrderInterface $platformOrder
     * @param $orderId
     * @return Order
     * @throws InvalidParamException
     * @throws NotFoundException
     */
    public function createFromPlatformData(
        PlatformOrderInterface $platformOrder,
        $orderId
    ) {
        $order = new Order();

        $order->setMundipaggId(new OrderId($orderId));

        $baseStatus = explode('_', $platformOrder->getStatus());
        $status = $baseStatus[0];
        for ($i = 1; $i < count($baseStatus); $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        if ($platformOrder->getCode() === null) {
            throw new NotFoundException("Order not found: {$orderId}");
        }

        try {
            OrderStatus::$status();
        } catch (Throwable $e) {
            throw new InvalidParamException(
                "Invalid order status!",
                $status
            );
        }
        $order->setStatus(OrderStatus::$status());
        $order->setPlatformOrder($platformOrder);

        return $order;
    }

    public function createFromSubscriptionData(
        Subscription $subscription,
        $platformOrderStatus
    ) {
        $order = new Order();

        try {
            OrderStatus::$platformOrderStatus();
        } catch (Throwable $e) {
            throw new InvalidParamException(
                "Invalid order status!",
                $platformOrderStatus
            );
        }

        $order->setStatus(OrderStatus::$platformOrderStatus());
        $order->setPlatformOrder($subscription->getPlatformOrder());

        if ($subscription->getCurrentCharge()) {
            $order->addCharge($subscription->getCurrentCharge());
        }

        if ($subscription->getCustomer()) {
            $order->setCustomer($subscription->getCustomer());
        }

        return $order;
    }
}
