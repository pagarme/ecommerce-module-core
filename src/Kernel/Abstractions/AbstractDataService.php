<?php

namespace Mundipagg\Core\Kernel\Abstractions;

use Mundipagg\Core\Kernel\Aggregates\Order;

abstract class AbstractDataService
{
    abstract public function updateAcquirerData(Order $order);
}