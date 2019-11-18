<?php

namespace Mundipagg\Core\Test\Recurrence;

use Mundipagg\Core\Recurrence\ValueObjects\PlanId;
use PHPUnit\Framework\TestCase;
use Mundipagg\Core\Recurrence\Factories\PlanFactory;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Zend\Db\Sql\Ddl\Column\Datetime;

class PlanFactoryTests extends TestCase
{
    public function testCreateFromPostDataShouldReturnAPlan()
    {
        $planFactory = new PlanFactory();

        $data = [
            'id' => 456654,
            'plan_id' => new PlanId('plan_45asDadb8Xd95451'),
            'billing_type' => 'PREPAID',
            'credit_card' => false,
            'boleto' => true,
            'allow_installments' => false,
            'product_id' => '8081',
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
            'status' => 'ACTIVE',
            'interval_type' => 'month',
            'interval_count' => 5,
        ];

        $result = $planFactory->createFromPostData($data);

        $this->assertInstanceOf(Plan::class, $result);
    }

    public function testCreateFromDbShouldReturnAPlan()
    {
        /** @todo Get a dbObject to test it */
    }
}
