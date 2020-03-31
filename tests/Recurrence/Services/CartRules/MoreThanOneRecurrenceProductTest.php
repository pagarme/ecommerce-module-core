<?php

namespace Mundipagg\Core\Test\Recurrence\Services\CartRules;

use Mundipagg\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Services\CartRules\CurrentProduct;
use Mundipagg\Core\Recurrence\Services\CartRules\MoreThanOneRecurrenceProduct;
use Mundipagg\Core\Recurrence\Services\CartRules\ProductListInCart;
use PHPUnit\Framework\TestCase;

class MoreThanOneRecurrenceProductTest extends TestCase
{
    public function testShouldReturnErrorIfTheConfigNotAllowHaveMoreThanOneRecurrenceProduct()
    {
        $currentProduct = $this->getCurrentProduct();
        $productListInCart = $this->getProductListInCart();

        $errorMessage = "You cant add more than one recurrence product on the same shopping cart";
        $recurrenceConfigMock = $this->getRecurrenceConfig(false, $errorMessage);

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
        $recurrenceConfigMock = $this->getRecurrenceConfig();

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

        $repetitionSelected->setId(2);

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

    protected function getRecurrenceConfig($allow = true, $error = "")
    {
        $recurrenceConfigMock = \Mockery::mock(RecurrenceConfig::class);

        $recurrenceConfigMock
            ->shouldReceive('getConflictMessageRecurrenceProductWithRecurrenceProduct')
            ->andReturn($error);

        if ($allow) {
            $recurrenceConfigMock
                ->shouldReceive('isPurchaseRecurrenceProductWithRecurrenceProduct')
                ->andReturnTrue();

            return $recurrenceConfigMock;
        }
        $recurrenceConfigMock
            ->shouldReceive('isPurchaseRecurrenceProductWithRecurrenceProduct')
            ->andReturnFalse();

        return $recurrenceConfigMock;
    }
}