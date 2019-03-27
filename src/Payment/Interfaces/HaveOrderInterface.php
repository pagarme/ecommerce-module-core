<?php

namespace Mundipagg\Core\Payment\Interfaces;

use Mundipagg\Core\Payment\Aggregates\Order;

interface HaveOrderInterface
{
    /**
     * @return Order
     */
    public function getOrder();

    /**
     * @param Order $order
     * @return mixed
     */
    public function setOrder(Order $order);
}