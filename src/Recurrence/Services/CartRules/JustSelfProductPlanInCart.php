<?php

namespace Mundipagg\Core\Recurrence\Services\CartRules;

use Mundipagg\Core\Recurrence\Services\CartRules\CurrentProduct;

class JustSelfProductPlanInCart implements RuleInterface
{
    /**
     * @var string
     */
    private $error;

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        if (
            !empty($productListInCart->getProductsPlan()) &&
            !$currentProduct->isNormalProduct()
        ) {
            $this->error = "You must have only one product plan in the cart";
        }
    }

    public function getError()
    {
        return $this->error;
    }
}