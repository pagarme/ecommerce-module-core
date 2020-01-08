<?php

namespace Mundipagg\Core\Test\Recurrence\Services;

use Mundipagg\Core\Recurrence\Services\InvoiceService;
use PHPUnit\Framework\TestCase;
use Mundipagg\Core\Test\Mock\Concrete\PlatformCoreSetup;

class Invoice extends TestCase
{
    public function testCancelShouldNotReturnAnError()
    {
        PlatformCoreSetup::bootstrap();
        $invoiceService  = new InvoiceService();
        //$this->assertNull($invoiceService->cancel(123));
    }
}