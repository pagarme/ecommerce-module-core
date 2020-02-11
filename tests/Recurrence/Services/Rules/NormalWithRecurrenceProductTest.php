<?php

namespace Mundipagg\Core\Test\Recurrence\Services\Rules;

use Mundipagg\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Services\Rules\CurrentProduct;
use Mundipagg\Core\Recurrence\Services\Rules\NormalWithRecurrenceProduct;
use Mundipagg\Core\Recurrence\Services\Rules\ProductListInCart;
use PHPUnit\Framework\TestCase;

class NormalWithRecurrenceProductTest extends TestCase
{
    public function testShouldReturnErrorWhenTryingToAddANormalProductInTheCartContainingRecurrenceProduct()
    {
        $currentProduct = $this->getCurrentProduct(true);
        $productListInCart = $this->getProductListInCart();

        $errorMessage = "You cant add a simple product and a recurrence product on the same shopping cart";
        $recurrenceConfigMock = $this->getRecurrenceConfig(false, $errorMessage);

        $rule = new NormalWithRecurrenceProduct($recurrenceConfigMock);
        $rule->run(
            $currentProduct,
            $productListInCart
        );

        $this->assertNotEmpty($rule->getError());
        $this->assertEquals($errorMessage, $rule->getError());
    }

    public function testShouldReturnErrorWhenTryingToAddARecurrenceProductInTheCartContainingNormalProduct()
    {
        $currentProduct = $this->getCurrentProduct();
        $productListInCart = $this->getProductListInCart();

        $errorMessage = "You cant add a simple product and a recurrence product on the same shopping cart";
        $recurrenceConfigMock = $this->getRecurrenceConfig(false, $errorMessage);

        $rule = new NormalWithRecurrenceProduct($recurrenceConfigMock);
        $rule->run(
            $currentProduct,
            $productListInCart
        );

        $this->assertNotEmpty($rule->getError());
        $this->assertEquals($errorMessage, $rule->getError());
    }

    public function testShouldNotReturnErrorBecauseIsConfiguredToAllowNormalAndRecurrenceProductAtTheSameShoppingCart()
    {
        $currentProduct = $this->getCurrentProduct();
        $productListInCart = $this->getProductListInCart();
        $recurrenceConfigMock = $this->getRecurrenceConfig();

        $rule = new NormalWithRecurrenceProduct($recurrenceConfigMock);
        $rule->run(
            $currentProduct,
            $productListInCart
        );

        $this->assertEmpty($rule->getError());
    }

    protected function getCurrentProduct($normal = false)
    {
        $currentProduct = new CurrentProduct();

        if ($normal) {
            $currentProduct->setIsNormalProduct(true);
            return $currentProduct;
        }

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

        $normalProduct = new \stdClass();
        $productList->addNormalProducts([$normalProduct]);

        return $productList;
    }

    protected function getRecurrenceConfig($allow = true, $error = "")
    {
        $recurrenceConfigMock = \Mockery::mock(RecurrenceConfig::class);

        $recurrenceConfigMock
            ->shouldReceive('getConflictMessageRecurrenceProductWithNormalProduct')
            ->andReturn($error);

        if ($allow) {
            $recurrenceConfigMock
                ->shouldReceive('isPurchaseRecurrenceProductWithNormalProduct')
                ->andReturnTrue();

            return $recurrenceConfigMock;
        }

        $recurrenceConfigMock
            ->shouldReceive('isPurchaseRecurrenceProductWithNormalProduct')
            ->andReturnFalse();

        return $recurrenceConfigMock;
    }
}