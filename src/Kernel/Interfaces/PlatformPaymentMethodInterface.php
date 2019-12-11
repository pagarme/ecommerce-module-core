<?php

namespace Mundipagg\Core\Kernel\Interfaces;

interface PlatformPaymentMethodInterface
{
    public function setPaymentMethod($paymentMethod);
    public function getPaymentMethod();
}