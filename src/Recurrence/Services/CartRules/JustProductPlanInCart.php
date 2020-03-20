<?php

namespace Mundipagg\Core\Recurrence\Services\CartRules;

use Mundipagg\Core\Recurrence\Services\CartRules\CurrentProduct;

class JustProductPlanInCart implements RuleInterface
{
    /**
     * @var string
     */
    private $error;

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        $foundError = false;

        if (
            !$currentProduct->isNormalProduct() &&
            (
                !empty($productListInCart->getNormalProducts()) ||
                !empty($productListInCart->getProductsPlan())
            )
        ) {
            $foundError = true;
        }

        if (!empty($productListInCart->getProductsPlan())) {
            $foundError = true;
        }

        if (!empty($productListInCart->getRecurrenceProducts())) {
            $foundError = true;
        }

        if ($foundError) {
            $this->error = "It's not possible to have any" .
                "other product with a product plan";
        }
    }

    public function getError()
    {
        return $this->error;
    }
}