<?php

namespace Mundipagg\Core\Kernel;

use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;

abstract class AbstractPlatformOrderDecorator implements PlatformOrderInterface
{
    protected $platformOrder;

    public function __construct($platformOrder)
    {
        $this->platformOrder = $platformOrder;
    }

    public function getPlatformOrder()
    {
        return $this->platformOrder;
    }
}