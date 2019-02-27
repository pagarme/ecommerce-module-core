<?php

namespace Mundipagg\Core\Kernel\Services;

final class OrderLogService extends LogService
{
    public function __construct($stackTraceDepth = 3, $addHost = true)
    {
        parent::__construct('Order', $addHost);
        $this->stackTraceDepth = $stackTraceDepth;
    }

    public function orderInfo($orderCode, $message, $sourceObject = null)
    {
        $orderMessage = "Order #$orderCode : $message";
        parent::info($orderMessage, $sourceObject);
    }
}