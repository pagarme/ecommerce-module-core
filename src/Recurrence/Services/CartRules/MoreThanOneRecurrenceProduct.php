<?php

namespace Mundipagg\Core\Recurrence\Services\CartRules;

use Mundipagg\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;

class MoreThanOneRecurrenceProduct implements RuleInterface
{
    /**
     * @var RecurrenceConfig
     */
    protected $recurrenceConfig;
    private $error;

    CONST DEFAULT_MESSAGE = "It's not possible to add ".
    "recurrence product with another recurrence product";

    public function __construct(RecurrenceConfig $recurrenceConfig)
    {
        $this->recurrenceConfig = $recurrenceConfig;
    }

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        $canAddRecurrenceProductWithRecurrenceProduct =
            $this->recurrenceConfig
                ->isPurchaseRecurrenceProductWithRecurrenceProduct();

        $messageConflictRecurrence =
            $this->recurrenceConfig
                ->getConflictMessageRecurrenceProductWithRecurrenceProduct();

        if (empty($messageConflictRecurrence)) {
            $messageConflictRecurrence = self::DEFAULT_MESSAGE;
        }

        $sameRecurrenceProduct = $this->checkIsSameRecurrenceProduct(
            $currentProduct,
            $productListInCart
        );

        if (
            !$canAddRecurrenceProductWithRecurrenceProduct &&
            (
                !$currentProduct->isNormalProduct() &&
                !empty($productListInCart->getRecurrenceProducts())
            ) &&
            !$sameRecurrenceProduct
        ) {
            $this->setError($messageConflictRecurrence);
        }

        return;
    }

    /**
     * @param CurrentProduct $currentProduct
     * @param ProductListInCart $productListInCart
     * @return bool
     */
    private function checkIsSameRecurrenceProduct(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        foreach ($productListInCart->getRecurrenceProducts() as $product) {
            $productSubscriptionSelected =
                $currentProduct->getProductSubscriptionSelected();

            $repetionSelected = $currentProduct->getRepetitionSelected();

            if (
                ($product->getProductId() == $productSubscriptionSelected->getProductId()) &&
                ($repetionSelected->getId() == $productListInCart->getRepetition()->getId())
            ) {
                return true;
            }
        }

        return false;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function setError($error)
    {
        $this->error = $error;
    }
}