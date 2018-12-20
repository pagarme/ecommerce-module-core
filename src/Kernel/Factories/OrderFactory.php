<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Throwable;

class OrderFactory implements FactoryInterface
{
    /**
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
        }catch(Throwable $e) {
            throw new NotFoundException("Invalid order status: $status");
        }

        $order
            ->setCode($postData['code'])
            ->setAmount($postData['amount'])
            ->setStatus(OrderStatus::$status());

        $charges = $postData['charges'];

        $chargeFactory = new ChargeFactory();

        foreach($charges as $charge) {
            $newCharge = $chargeFactory->createFromPostData($charge);
            $order->addCharge($newCharge);
        }

        return $order;
    }
}