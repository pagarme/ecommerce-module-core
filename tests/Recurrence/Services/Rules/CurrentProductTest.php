<?php

namespace Mundipagg\Core\Test\Recurrence\Services\Rules;

use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Mundipagg\Core\Recurrence\Interfaces\RepetitionInterface;
use Mundipagg\Core\Recurrence\Services\Rules\CurrentProduct;
use PHPUnit\Framework\TestCase;

class CurrentProductTest extends TestCase
{
    public function testSholdReturnARecurrenceProductOnCurrentProduct()
    {
        $currentProduct = new CurrentProduct();

        $productSubscription = new ProductSubscription();
        $repetitionSelected = new Repetition();

        $currentProduct->setProductSubscriptionSelected($productSubscription);
        $currentProduct->setRepetitionSelected($repetitionSelected);

        $this->assertInstanceOf(RepetitionInterface::class, $currentProduct->getRepetitionSelected());
        $this->assertInstanceOf(ProductSubscriptionInterface::class, $currentProduct->getProductSubscriptionSelected());
        $this->assertFalse($currentProduct->isNormalProduct());
    }

    public function testSholdReturnANormalProductInCurrentProduct()
    {
        $currentProduct = new CurrentProduct();
        $currentProduct->setIsNormalProduct(true);

        $this->assertTrue($currentProduct->isNormalProduct());
    }
}