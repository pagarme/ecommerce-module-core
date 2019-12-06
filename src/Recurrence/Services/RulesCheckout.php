<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;

class RulesCheckout
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

        $productSubscriptionCompatible = $productSubscriptionInCart->checkProductHasSameMethodPayment(
            $productSubscriptionSelected
        );

        return $repetitionCompatible && $productSubscriptionCompatible;
    }
}
