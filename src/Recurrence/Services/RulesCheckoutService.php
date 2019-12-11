<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;

class RulesCheckoutService
{
    public function runRulesCheckoutSubscription(
        ProductSubscription $productSubscriptionInCart,
        ProductSubscription $productSubscriptionSelected,
        Repetition $repetitionInCart,
        Repetition $repetitionSelected
    ) {
        $repetitionCompatible = $repetitionInCart->checkRepetitionIsCompatible(
            $repetitionSelected
        );

        $productSubscriptionCompatible = $productSubscriptionInCart->checkProductHasSamePaymentMethod(
            $productSubscriptionSelected
        );

        return $repetitionCompatible && $productSubscriptionCompatible;
    }
}
