<?php

namespace Mundipagg\Core\Test\Recurrence\Aggregates;

use Mundipagg\Core\Recurrence\Aggregates\Plan;
use PHPUnit\Framework\TestCase;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Zend\Db\Sql\Ddl\Column\Datetime;

class PlanTests extends TestCase
{
    private $plan;

    protected function setUp()
    {
        $this->plan = new Plan();
    }

    public function testJsonSerializeShouldReturnAnInstanceOfStdClass()
    {
        $this->assertInstanceOf(\stdClass::class, $this->plan->jsonSerialize());
    }

    public function testJsonSerializeShouldSetAllProperties()
    {
        $id = '1';
        $interval = IntervalValueObject::month(2);
        $planId = 'plan_abcdefgh';
        $productId = '4123';
        $creditCard = true;
        $boleto = false;
        $status = 'ACTIVE';
        $billingType = 'PREPAID';
        $allowInstallments = true;
        $createdAt = new \Datetime();
        $updatedAt = new \Datetime();

        $this->plan->setId($id);
        $this->assertEquals($this->plan->getId(), $id);

        $this->plan->setInterval($interval);
        $this->assertEquals($this->plan->getInterval(), $interval);

        $this->plan->setPlanId($planId);
        $this->assertEquals($this->plan->getPlanId(), $planId);

        $this->plan->setProductId($productId);
        $this->assertEquals($this->plan->getProductId(), $productId);

        $this->plan->setCreditCard($creditCard);
        $this->assertEquals($this->plan->getCreditCard(), $creditCard);

        $this->plan->setBoleto($boleto);
        $this->assertEquals($this->plan->getBoleto(), $boleto);

        $this->plan->setStatus($status);
        $this->assertEquals($this->plan->getStatus(), $status);

        $this->plan->setBillingType($billingType);
        $this->assertEquals($this->plan->getBillingType(), $billingType);

        $this->plan->setAllowInstallments($allowInstallments);
        $this->assertEquals($this->plan->getAllowInstallments(), $allowInstallments);

        $this->plan->setCreatedAt($createdAt);
        $this->assertInternalType('string', $this->plan->getCreatedAt());

        $this->plan->setUpdatedAt($updatedAt);
        $this->assertInternalType('string', $this->plan->getUpdatedAt());
    }
}
