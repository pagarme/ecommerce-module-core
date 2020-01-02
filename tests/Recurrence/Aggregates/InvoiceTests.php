<?php

namespace Mundipagg\Core\Test\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\ValueObjects\Id\InvoiceId;
use Mundipagg\Core\Payment\Aggregates\Customer;
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

    public function testInvoiceObject()
    {
        $this->invoice->setMundipaggId(new InvoiceId('in_45asDadb8Xd95451'));
        $this->invoice->setId(1);
        $this->invoice->setCustomer(new Customer());
        $this->invoice->setPaymentMethod('credit_card');
        $this->invoice->setStatus('paid');

        $this->assertEquals('in_45asDadb8Xd95451', $this->invoice->getMundipaggId()->getValue());
        $this->assertEquals(1, $this->invoice->getId());
        $this->assertEquals('credit_card', $this->invoice->getPaymentMethod());
        $this->assertEquals('paid', $this->invoice->getStatus());

    }
}
