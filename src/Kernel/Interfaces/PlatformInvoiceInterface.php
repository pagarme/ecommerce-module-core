<?php

namespace Mundipagg\Core\Kernel\Interfaces;

use Mundipagg\Core\Kernel\ValueObjects\InvoiceState;

interface PlatformInvoiceInterface
{
    public function save();
    public function setState(InvoiceState $state);
    public function loadByIncrementId($incrementId);
    public function getIncrementId();
    public function prepareFor(PlatformOrderInterface $order);
    public function createFor(PlatformOrderInterface $order);
    public function getPlatformInvoice();
    public function canRefund();
    public function isCanceled();

}