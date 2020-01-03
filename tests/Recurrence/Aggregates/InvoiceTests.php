<?php

namespace Mundipagg\Core\Test\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\ValueObjects\Id\InvoiceId;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Recurrence\Aggregates\Cycle;
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
        $this->invoice->setAmount(100);
        $this->invoice->setCharge(new Charge());
        $this->invoice->setInstallments(true);
        $this->invoice->setCycle(new Cycle());
        $this->invoice->setSubscriptionId(new SubscriptionId('sub_hdgeifuaudiv9ek3'));
        $this->invoice->setTotalDiscount(100);
        $this->invoice->setTotalIncrement(100);

        $this->assertEquals('in_45asDadb8Xd95451', $this->invoice->getMundipaggId()->getValue());
        $this->assertEquals(1, $this->invoice->getId());
        $this->assertEquals('credit_card', $this->invoice->getPaymentMethod());
        $this->assertEquals('paid', $this->invoice->getStatus());
        $this->assertEquals(100, $this->invoice->getAmount());
        $this->assertEquals(100, $this->invoice->getTotalDiscount());
        $this->assertEquals(100, $this->invoice->getTotalIncrement());
        $this->assertContainsOnlyInstancesOf(Customer::class, [$this->invoice->getCustomer()]);
        $this->assertContainsOnlyInstancesOf(Charge::class, [$this->invoice->getCharge()]);
        $this->assertContainsOnlyInstancesOf(Cycle::class, [$this->invoice->getCycle()]);
        $this->assertContainsOnlyInstancesOf(SubscriptionId::class, [$this->invoice->getSubscriptionId()]);
        $this->assertContainsOnly('boolean', [$this->invoice->getInstallments()]);
    }
}
