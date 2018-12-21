<?php

namespace Mundipagg\Core\Kernel\Interfaces;

use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;

interface PlatformOrderInterface
{
    public function save();
    public function setState(OrderState $state);
    public function setStatus(OrderStatus $status);
    public function loadByIncrementId($incrementId);
    public function addHistoryComment($message);
    public function setIsCustomerNotified();
    public function canInvoice();
    public function getPlatformOrder();
    public function getIncrementId();
}