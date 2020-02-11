<?php

namespace Mundipagg\Core\Test\Recurrence\Services\Rules;

use Mundipagg\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Services\Rules\CurrentProduct;
use Mundipagg\Core\Recurrence\Services\Rules\MoreThanOneRecurrenceProduct;
use Mundipagg\Core\Recurrence\Services\Rules\ProductListInCart;
use PHPUnit\Framework\TestCase;

class MoreThanOneRecurrenceProductTest extends TestCase
{
    public function testShouldReturnErrorIfTheConfigNotAllowHaveMoreThanOneRecurrenceProduct()
    {
        $currentProduct = $this->getCurrentProduct();
        $productListInCart = $this->getProductListInCart();

        $recurrenceConfigMock = \Mockery::mock(RecurrenceConfig::class);

        $recurrenceConfigMock
            ->shouldReceive('isPurchaseRecurrenceProductWithRecurrenceProduct')
            ->andReturnFalse();

        $errorMessage = "You cant add more than one recurrence product on the same shopping cart";

        $recurrenceConfigMock
            ->shouldReceive('getConflictMessageRecurrenceProductWithRecurrenceProduct')
            ->andReturn($errorMessage);

        $rule = new MoreThanOneRecurrenceProduct($recurrenceConfigMock);
        $rule->run(
            $currentProduct,
            $productListInCart
        );

        $this->assertNotEmpty($rule->getError());
        $this->assertEquals($errorMessage, $rule->getError());
    }

    public function testShouldNotReturnErrorBecauseTheConfigAllowHaveMoreThanOneRecurrenceProduct()
    {
        $currentProduct = $this->getCurrentProduct();
        $productListInCart = $this->getProductListInCart();

        $recurrenceConfigMock = \Mockery::mock(RecurrenceConfig::class);

        $recurrenceConfigMock
            ->shouldReceive('isPurchaseRecurrenceProductWithRecurrenceProduct')
            ->andReturnTrue();

        $recurrenceConfigMock
            ->shouldReceive('getConflictMessageRecurrenceProductWithRecurrenceProduct')
            ->andReturn("");

        $rule = new MoreThanOneRecurrenceProduct($recurrenceConfigMock);
        $rule->run(
            $currentProduct,
            $productListInCart
        );

        $this->assertEmpty($rule->getError());
    }

    protected function getCurrentProduct()
    {
        $currentProduct = new CurrentProduct();

        $productSubscription = new ProductSubscription();
        $repetitionSelected = new Repetition();

        $currentProduct->setProductSubscriptionSelected($productSubscription);
        $currentProduct->setRepetitionSelected($repetitionSelected);

        return $currentProduct;
    }

    protected function getProductListInCart()
    {
        $productList = new ProductListInCart();

        $productSubscription = new ProductSubscription();
        $repetition = new Repetition();

        $productList->addRecurrenceProduct($productSubscription);
        $productList->setRecurrenceProduct($productSubscription);
        $productList->setRepetition($repetition);

        return $productList;
    }
}