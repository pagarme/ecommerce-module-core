<?php

namespace Mundipagg\Core\Recurrence\Services\CartRules;

use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Recurrence\Services\CartRules\CurrentProduct;

class JustOneProductPlanInCart implements RuleInterface
{
    /**
     * @var string
     */
    private $error;

    /**
     * @var LocalizationService
     */
    private $i18n;

    /**
     * JustOneProductPlanInCart constructor.
     */
    public function __construct()
    {
        $this->i18n = new LocalizationService();
    }


    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        if ($currentProduct->getQuantity() > 1) {
            $this->error = $this->i18n->getDashboard(
                'Must be has one product plan on cart'
            );
        }
    }

    public function getError()
    {
        return $this->error;
    }
}