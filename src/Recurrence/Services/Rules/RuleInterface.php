<?php

namespace Mundipagg\Core\Recurrence\Services\Rules;

interface RuleInterface
{
    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    );

    public function setError($error);
    public function getError();
}