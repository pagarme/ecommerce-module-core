<?php

namespace Mundipagg\Core\Test\Recurrence\Services\CartRules;

use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Services\CartRules\CurrentProduct;
use Mundipagg\Core\Recurrence\Services\CartRules\ProductListInCart;
use PHPUnit\Framework\TestCase;

class CompatibleRecurrenceProductsTest extends  TestCase
{
    public function testShouldReturnErrorBecauseThePaymentMethodIsIncompatible()
    {
        $productList = new ProductListInCart();

        $productSubscriptionInCart = new ProductSubscription();
        $productSubscriptionInCart->setBoleto(true);
        $productSubscriptionInCart->setCreditCard(true);

        $repetition = new Repetition();

        $productList->addRecurrenceProduct($productSubscriptionInCart);
        $productList->setRecurrenceProduct($productSubscriptionInCart);
        $productList->setRepetition($repetition);

        $currentProduct = new CurrentProduct();

        $productSubscriptionSelected = new ProductSubscription();
        $productSubscriptionSelected->setBoleto(false);
        $productSubscriptionSelected->setCreditCard(false);

        $repetitionSelected = new Repetition();

        $currentProduct->setProductSubscriptionSelected($productSubscriptionSelected);
        $currentProduct->setRepetitionSelected($repetitionSelected);

        $errorExpected = "The recurrence products should have the same payment configuration and the same interval";

        $rule = \Mockery::mock(
            'Mundipagg\Core\Recurrence\Services\CartRules\CompatibleRecurrenceProducts[getMessageConflict]'
        );

        $rule->shouldReceive('getMessageConflict')
            ->andReturn($errorExpected);

        $rule->run(
            $currentProduct,
            $productList
        );

        $this->assertEquals($errorExpected, $rule->getError());

    }

    public function testShouldReturnErrorBecauseTheRepetitionIsIncompatible()
    {
        $productList = new ProductListInCart();

        $productSubscriptionInCart = new ProductSubscription();

        $repetitionInCart = new Repetition();
        $repetitionInCart->setIntervalCount(2);
        $repetitionInCart->setInterval('month');

        $productList->addRecurrenceProduct($productSubscriptionInCart);
        $productList->setRecurrenceProduct($productSubscriptionInCart);
        $productList->setRepetition($repetitionInCart);

        $currentProduct = new CurrentProduct();

        $productSubscriptionSelected = new ProductSubscription();

        $repetitionSelected = new Repetition();
        $repetitionSelected->setIntervalCount(3);
        $repetitionSelected->setInterval('year');

        $currentProduct->setProductSubscriptionSelected($productSubscriptionSelected);
        $currentProduct->setRepetitionSelected($repetitionSelected);

        $errorExpected = "The recurrence products should have the same payment configuration and the same interval";

        $rule = \Mockery::mock(
            'Mundipagg\Core\Recurrence\Services\CartRules\CompatibleRecurrenceProducts[getMessageConflict]'
        );

        $rule->shouldReceive('getMessageConflict')
            ->andReturn($errorExpected);

        $rule->run(
            $currentProduct,
            $productList
        );

        $this->assertEquals($errorExpected, $rule->getError());
    }


    public function testShouldNotReturnErrorBecauseTheRecurrenceProductsAreCompatibles()
    {
        $productSubscriptionInCart = new ProductSubscription();
        $productSubscriptionInCart->setBoleto(true);
        $productSubscriptionInCart->setCreditCard(true);

        $productSubscriptionSelected = new ProductSubscription();
        $productSubscriptionSelected->setBoleto(true);
        $productSubscriptionSelected->setCreditCard(true);

        $repetitionInCart = new Repetition();
        $repetitionInCart->setIntervalCount(2);
        $repetitionInCart->setInterval('month');

        $repetitionSelected = new Repetition();
        $repetitionSelected->setIntervalCount(2);
        $repetitionSelected->setInterval('month');

        $productList = new ProductListInCart();
        $productList->addRecurrenceProduct($productSubscriptionInCart);
        $productList->setRecurrenceProduct($productSubscriptionInCart);
        $productList->setRepetition($repetitionInCart);

        $currentProduct = new CurrentProduct();
        $currentProduct->setProductSubscriptionSelected($productSubscriptionSelected);
        $currentProduct->setRepetitionSelected($repetitionSelected);

        $rule = \Mockery::mock(
            'Mundipagg\Core\Recurrence\Services\CartRules\CompatibleRecurrenceProducts[getMessageConflict]'
        );

        $rule->shouldReceive('getMessageConflict')
            ->andReturnNull();

        $rule->run(
            $currentProduct,
            $productList
        );

        $this->assertEmpty($rule->getError());
    }

    public function testShouldNotReturnErrorBecauseDoesNotHaveRecurrenceProducts()
    {
        $productList = new ProductListInCart();
        $currentProduct = new CurrentProduct();

        $rule = \Mockery::mock(
            'Mundipagg\Core\Recurrence\Services\CartRules\CompatibleRecurrenceProducts[getMessageConflict]'
        );

        $rule->shouldReceive('getMessageConflict')
            ->andReturnNull();

        $rule->run(
            $currentProduct,
            $productList
        );

        $this->assertEmpty($rule->getError());
    }
}