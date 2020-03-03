<?php

namespace Mundipagg\Core\Test\Recurrence\Aggregates;

use Mockery;
use MundiAPILib\Models\CreateSubscriptionRequest;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod;
use Mundipagg\Core\Payment\Aggregates\Address;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Shipping;
use Mundipagg\Core\Payment\ValueObjects\Phone;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Recurrence\Aggregates\Cycle;
use Mundipagg\Core\Recurrence\Aggregates\Increment;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\ValueObjects\PlanId;
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
        $this->subscription->setCurrentCharge(new Charge());
        $this->subscription->setPlanId(Mockery::mock(PlanId::class));
        $this->subscription->setCustomer(new Customer());
        $this->subscription->setItems([new SubProduct]);
        $this->subscription->setShipping(new Shipping);
        $this->subscription->setIncrement(new Increment);
        $this->subscription->setStatementDescriptor("Statement Descriptor");
        $this->subscription->setCurrentCycle(new Cycle());

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
        $this->assertEquals("Statement Descriptor", $this->subscription->getStatementDescriptor());

        $this->assertEquals(Subscription::RECURRENCE_TYPE, $this->subscription->getRecurrenceType());
        $this->assertEquals(SubscriptionStatus::active(), $this->subscription->getStatus());
        $this->assertEquals(PaymentMethod::CREDIT_CARD, $this->subscription->getPaymentMethod());
        $this->assertInstanceOf(SubscriptionId::class, $this->subscription->getMundipaggId());
        $this->assertInstanceOf(SubscriptionId::class, $this->subscription->getSubscriptionId());
        $this->assertInstanceOf(PlatformOrderInterface::class, $this->subscription->getPlatformOrder());
        $this->assertInstanceOf(Invoice::class, $this->subscription->getInvoice());
        $this->assertInstanceOf(Charge::class, $this->subscription->getCurrentCharge());
        $this->assertInstanceOf(PlanId::class, $this->subscription->getPlanId());
        $this->assertInstanceOf(Customer::class, $this->subscription->getCustomer());
        $this->assertInstanceOf(Shipping::class, $this->subscription->getShipping());
        $this->assertInstanceOf(Increment::class, $this->subscription->getIncrement());
        $this->assertContainsOnlyInstancesOf(SubProduct::class, $this->subscription->getItems());
        $this->assertInstanceOf(Cycle::class, $this->subscription->getCurrentCycle());
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

    public function testShouldAddChargesOnSubscription()
    {
        $charge = new Charge();
        $charge->setMundipaggId(new ChargeId("ch_1234567890123456"));

        $charge2 = new Charge();
        $charge2->setMundipaggId(new ChargeId("ch_abcdefghijklmnop"));

        $this->subscription->addCharge($charge);
        $this->subscription->addCharge($charge2);

        $this->assertContainsOnlyInstancesOf(Charge::class, $this->subscription->getCharges());
        $this->assertCount(2, $this->subscription->getCharges());
    }

    public function testShouldRetrurnAnEmptyArrayBecauseDoesNotHaveAnCharge()
    {
        $this->assertEmpty($this->subscription->getCharges());
    }

    public function testShouldNotAddAnChargeTwice()
    {
        $charge = new Charge();
        $charge->setMundipaggId(new ChargeId("ch_1234567890123456"));

        $this->subscription->addCharge($charge);
        $this->subscription->addCharge($charge);

        $this->assertContainsOnlyInstancesOf(Charge::class, $this->subscription->getCharges());
        $this->assertCount(1, $this->subscription->getCharges());
    }

    public function testShouldSetAnArrayOfChargesOnSubscription()
    {
        $charge = new Charge();
        $charge->setMundipaggId(new ChargeId("ch_1234567890123456"));

        $charge2 = new Charge();
        $charge2->setMundipaggId(new ChargeId("ch_abcdefghijklmnop"));

        $this->subscription->setCharges([
            $charge,
            $charge2
        ]);

        $this->assertContainsOnlyInstancesOf(Charge::class, $this->subscription->getCharges());
        $this->assertCount(2, $this->subscription->getCharges());
    }
}