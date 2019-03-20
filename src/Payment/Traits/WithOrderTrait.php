<?php

namespace Mundipagg\Core\Payment\Traits;

use Mundipagg\Core\Payment\Aggregates\Order;

trait WithOrderTrait
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }
}