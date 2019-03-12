<?php

namespace Mundipagg\Core\Payment\Interfaces;

use Mundipagg\Core\Payment\Aggregates\Order as PaymentOrder;

interface ResponseHandlerInterface
{
    public function handle($response, PaymentOrder $paymentOrder = null);
}