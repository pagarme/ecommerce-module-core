<?php

namespace Mundipagg\Core\Recurrence\Services\CartRules;

use Mundipagg\Core\Recurrence\Services\CartRules\CurrentProduct;

class JustOneProductPlanInCart implements RuleInterface
{
    /**
     * @var string
     */
    private $error;

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        if ($currentProduct->getQuantity() > 1) {
            $this->error = "Must be has one product plan on cart";
        }
    }

    public function getError()
    {
        return $this->error;
    }
}