<?php

namespace Mundipagg\Core\Test\Recurrence;

use PHPUnit\Framework\TestCase;
use Mundipagg\Core\Recurrence\Factories\PlanFactory;

class PlanFactoryTests extends TestCase
{
    public function testCreateFromPostDataShouldReturnAPlan()
    {
        $planFactory = new PlanFactory();

        $data = [
            'interval',
            'interval_count',
            'plan_id',
            'product_id',
            'credit_card',
            'boleto',
            'status',
            'billing_type',
            'allow_installments'
        ];

        $result = $planFactory->createFromPostData($data);

        $this->assertEquals(1, is_object($result));
    }
}
