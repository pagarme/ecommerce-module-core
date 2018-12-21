<?php

namespace Mundipagg\Core\Kernel\Abstractions;

use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;

abstract class AbstractPlatformOrderDecorator implements PlatformOrderInterface
{
    protected $platformOrder;

    public function addHistoryComment($message)
    {
        $message = 'MP - ' . $message;
        $this->addMPHistoryComment($message);
    }

    public function getPlatformOrder()
    {
        return $this->platformOrder;
    }

    abstract protected function addMPHistoryComment($message);
}