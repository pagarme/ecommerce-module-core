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
    public function payAmount($amount);
    public function getGrandTotal();
    public function getTotalPaid();
    public function getTotalDue();
    public function setTotalPaid($amount);
    public function setBaseTotalPaid($amount);
    public function setTotalDue($amount);
    public function setBaseTotalDue($amount);
}