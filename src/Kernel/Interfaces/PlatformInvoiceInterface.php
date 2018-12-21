<?php

namespace Mundipagg\Core\Kernel\Interfaces;

use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;

interface PlatformInvoiceInterface
{
    public function save();
    //public function setState(OrderState $state);
    // public function setStatus(OrderStatus $status);
    public function loadByIncrementId($incrementId);
    public function getIncrementId();
    public function prepareFor(PlatformOrderInterface $order);
    public function createFor(PlatformOrderInterface $order);
    public function getPlatformInvoice();
    // public function addHistoryComment($message);
    //public function setIsCustomerNotified();
    // public function canInvoice();
}