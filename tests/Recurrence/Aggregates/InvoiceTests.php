<?php

namespace Mundipagg\Core\Test\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\ValueObjects\Id\InvoiceId;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;
use PHPUnit\Framework\TestCase;

class InvoiceTests extends TestCase
{
    /**
     * @var Invoice
     */
    private $invoice;

    protected function setUp()
    {
        $this->invoice = new Invoice();
    }

    public function testCycleObject()
    {
        $this->invoice->setMundipaggId(new InvoiceId('in_45asDadb8Xd95451'));
        $this->invoice->setId(1);

        $this->assertEquals('in_45asDadb8Xd95451', $this->invoice->getMundipaggId()->getValue());
        $this->assertEquals(1, $this->invoice->getId());
    }
}
