<?php

namespace Mundipagg\Core\Kernel\Abstractions;

use Mundipagg\Core\Kernel\Interfaces\PlatformInvoiceInterface;

abstract class AbstractInvoiceDecorator implements PlatformInvoiceInterface
{
    protected $platformInvoice;

    public function __construct($platformInvoice = null)
    {
        $this->platformInvoice = $platformInvoice;
    }

    public function getPlatformInvoice()
    {
        return $this->platformInvoice;
    }
}