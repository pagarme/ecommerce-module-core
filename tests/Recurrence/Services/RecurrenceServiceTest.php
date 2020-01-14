<?php

namespace Mundipagg\Core\Test\Recurrence\Services;

use Mundipagg\Core\Recurrence\Factories\ProductSubscriptionFactory;
use Mundipagg\Core\Recurrence\Repositories\ProductSubscriptionRepository;
use Mundipagg\Core\Recurrence\Services\RecurrenceService;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Test\Abstractions\AbstractSetupTest;

class RecurrenceServiceTest extends AbstractSetupTest
{
    public function testShouldReturnEmptyWhenTheRecurrenceProductNotExists()
    {
        $recurrenceService = new RecurrenceService();
        $this->assertEmpty($recurrenceService->getRecurrenceProductByProductId(10));
    }

    public function testShouldReturnARecurrenceProductByProductId()
    {
        $recurrenceProduct = $this->insertProductSubscription();

        $recurrenceService = new RecurrenceService();
        $this->assertNotEmpty($recurrenceService->getRecurrenceProductByProductId($recurrenceProduct->getProductId()));
    }

    public function testShouldReturnMaxInstallmentByIntervalTypeMonth()
    {
        $interval = IntervalValueObject::month(7);

        $recurrenceService = new RecurrenceService();
        $maxInstallment = $recurrenceService->getMaxInstallmentByRecurrenceInterval($interval);

        $this->assertEquals(7, $maxInstallment);
    }

    public function testShouldReturnMaxInstallmentByIntervalTypeYear()
    {
        $interval = IntervalValueObject::year(2);

        $recurrenceService = new RecurrenceService();
        $maxInstallment = $recurrenceService->getMaxInstallmentByRecurrenceInterval($interval);

        $this->assertEquals(12, $maxInstallment);
    }

    private function insertProductSubscription()
    {
        $product = [
            "product_id" => "23",
            "boleto" => true,
            "credit_card" => true,
            "allow_installments" => true,
            "sell_as_normal_product" => true,
            "cycles" => 10,
            "repetitions" => [
                [
                    "interval_count" => 1,
                    "interval" => "month",
                    "recurrence_price"=> 50000
                ],
                [
                    "interval_count" => 2,
                    "interval" => "month",
                    "recurrence_price" => 45000
                ]
            ]
        ];

        $factory = new ProductSubscriptionFactory();
        $productSubscription = $factory->createFromPostData($product);

        $repo = new ProductSubscriptionRepository();
        $repo->save($productSubscription);

        return $productSubscription;
    }
}