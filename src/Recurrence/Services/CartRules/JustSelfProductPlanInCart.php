<?php

namespace Mundipagg\Core\Recurrence\Services\CartRules;

use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Recurrence\Services\CartRules\CurrentProduct;

class JustSelfProductPlanInCart implements RuleInterface
{
    /**
     * @var string
     */
    private $error;

    /**
     * @var LocalizationService
     */
    private $i18n;

    public function __construct()
    {
        $this->i18n = new LocalizationService();
    }

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        if (
            !empty($productListInCart->getProductsPlan()) &&
            !$currentProduct->isNormalProduct()
        ) {
            $this->error = $this->i18n->getDashboard(
                "You must have only one product plan in the cart"
            );
        }
    }

    public function getError()
    {
        return $this->error;
    }
}