<?php

namespace Mundipagg\Core\Test\Recurrence\Aggregates;

use Mockery;
use MundiAPILib\Models\CreateSubscriptionRequest;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod;
use Mundipagg\Core\Payment\Aggregates\Address;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Shipping;
use Mundipagg\Core\Payment\ValueObjects\Phone;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Recurrence\Aggregates\Increment;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\ValueObjects\Id\PlanId;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    /**
     * @var Subscription
     */
    private $subscription;

    protected function setUp()
    {
        $this->subscription = new Subscription();
        parent::setUp();
    }

    public function testSubscriptionObject()
    {
        $this->subscription->setId(1);
        $this->subscription->setCode("1234");
        $this->subscription->setInstallments(1);
        $this->subscription->setIntervalType('month');
        $this->subscription->setIntervalCount(2);
        $this->subscription->setBillingType("PREPAID");
        $this->subscription->setCardToken("cardToken");
        $this->subscription->setCardId("cardId");
        $this->subscription->setBoletoDays(3);
        $this->subscription->setDescription("Description");
        $this->subscription->setStatus(SubscriptionStatus::active());
        $this->subscription->setPaymentMethod(PaymentMethod::credit_card());
        $this->subscription->setMundipaggId(Mockery::mock(SubscriptionId::class));
        $this->subscription->setSubscriptionId(Mockery::mock(SubscriptionId::class));
        $this->subscription->setPlatformOrder(Mockery::mock(PlatformOrderInterface::class));
        $this->subscription->setInvoice(Mockery::mock(Invoice::class));
        $this->subscription->setCharge(new Charge());
        $this->subscription->setPlanId(Mockery::mock(PlanId::class));
        $this->subscription->setCustomer(new Customer());
        $this->subscription->setItems([new SubProduct]);
        $this->subscription->setShipping(new Shipping);
        $this->subscription->setIncrement(new Increment);

        $this->assertEquals(1, $this->subscription->getId());
        $this->assertEquals("1234", $this->subscription->getCode());
        $this->assertEquals(1, $this->subscription->getInstallments());
        $this->assertEquals('month', $this->subscription->getIntervalType());
        $this->assertEquals(2, $this->subscription->getIntervalCount());
        $this->assertEquals("PREPAID", $this->subscription->getBillingType());
        $this->assertEquals("cardToken", $this->subscription->getCardToken());
        $this->assertEquals("cardId", $this->subscription->getCardId());
        $this->assertEquals(3, $this->subscription->getBoletoDays());
        $this->assertEquals("Description", $this->subscription->getDescription());

        $this->assertEquals(Subscription::RECURRENCE_TYPE, $this->subscription->getRecurrenceType());
        $this->assertEquals(SubscriptionStatus::active(), $this->subscription->getStatus());
        $this->assertEquals(PaymentMethod::CREDIT_CARD, $this->subscription->getPaymentMethod());
        $this->assertInstanceOf(SubscriptionId::class, $this->subscription->getMundipaggId());
        $this->assertInstanceOf(SubscriptionId::class, $this->subscription->getSubscriptionId());
        $this->assertInstanceOf(PlatformOrderInterface::class, $this->subscription->getPlatformOrder());
        $this->assertInstanceOf(Invoice::class, $this->subscription->getInvoice());
        $this->assertInstanceOf(Charge::class, $this->subscription->getCharge());
        $this->assertInstanceOf(PlanId::class, $this->subscription->getPlanId());
        $this->assertInstanceOf(Customer::class, $this->subscription->getCustomer());
        $this->assertInstanceOf(Shipping::class, $this->subscription->getShipping());
        $this->assertInstanceOf(Increment::class, $this->subscription->getIncrement());
        $this->assertContainsOnlyInstancesOf(SubProduct::class, $this->subscription->getItems());
    }

    public function testReturnSubscriptionObjectSerialized()
    {
        $this->assertJson(json_encode($this->subscription));
    }

    public function testShouldReturnStatusValue()
    {
        $this->subscription->setStatus(SubscriptionStatus::active());
        $this->assertEquals(SubscriptionStatus::ACTIVE, $this->subscription->getStatusValue());
    }

    public function testShouldReturnPlanIdValue()
    {
        $planId = new PlanId("plan_45asDadb8Xd95451");
        $this->subscription->setPlanId($planId);
        $this->assertEquals("plan_45asDadb8Xd95451", $this->subscription->getPlanIdValue());
    }

    public function testShouldReturnACreateSubscriptionRequestObject()
    {
        $this->subscription->setCustomer(new Customer());
        $this->subscription->setItems([new SubProduct]);

        $shipping = new Shipping;
        $shipping->setRecipientPhone(new Phone("021999999999"));
        $shipping->setAddress(new Address());

        $this->subscription->setShipping($shipping);

        $sdkObject = $this->subscription->convertToSdkRequest();
        $this->assertInstanceOf(CreateSubscriptionRequest::class, $sdkObject);
        $this->assertCount(1, $sdkObject->items);
    }

    public function testShouldReturnACreateSubscriptionRequestObjectWithoutItems()
    {
        $this->subscription->setCustomer(new Customer());

        $shipping = new Shipping;
        $shipping->setRecipientPhone(new Phone("021999999999"));
        $shipping->setAddress(new Address());

        $this->subscription->setShipping($shipping);

        $sdkObject = $this->subscription->convertToSdkRequest();
        $this->assertInstanceOf(CreateSubscriptionRequest::class, $sdkObject);
        $this->assertCount(0, $sdkObject->items);
    }
}