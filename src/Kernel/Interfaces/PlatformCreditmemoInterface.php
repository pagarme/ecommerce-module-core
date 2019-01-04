<?php

namespace Mundipagg\Core\Kernel\Interfaces;

interface PlatformCreditmemoInterface
{
    public function save();
    public function getIncrementId();
    public function prepareFor(PlatformOrderInterface $order);
    public function refund();
}