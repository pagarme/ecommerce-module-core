<?php

namespace Mundipagg\Core\Test\Recurrence\Services\Rules;

use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Mundipagg\Core\Recurrence\Interfaces\RepetitionInterface;
use Mundipagg\Core\Recurrence\Services\Rules\ProductListInCart;
use PHPUnit\Framework\TestCase;

class ProductListInCartTest extends TestCase
{
    public function testShouldCreateAnProductsListWithNormalProductsAndRecurrenceProducts()
    {
        $productList = new ProductListInCart();

        $normalProduct = new \stdClass();
        $productSubscription = new ProductSubscription();
        $repetition = new Repetition();

        $productList->addRecurrenceProduct($productSubscription);
        $productList->setRecurrenceProduct($productSubscription);
        $productList->setRepetition($repetition);
        $productList->addNormalProducts([$normalProduct]);

        $this->assertCount(1, $productList->getNormalProducts());
        $this->assertCount(1, $productList->getRecurrenceProducts());
        $this->assertInstanceOf(RepetitionInterface::class, $productList->getRepetition());
        $this->assertInstanceOf(ProductSubscriptionInterface::class, $productList->getRecurrenceProduct());
    }
}