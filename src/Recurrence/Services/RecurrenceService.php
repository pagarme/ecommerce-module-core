<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class RecurrenceService
{
    const MAX_INSTALLMENTS_NUMBER = 12;

    //@todo Change the function name because we've change the name of subscription product to recurrence product
    public function getRecurrenceProductByProductId($productId)
    {
        $productSubscription = $this->getProductSubscription($productId);
        if ($productSubscription !== null) {
            return $productSubscription;
        }
      
        return null;
    }

    public function getMaxInstallmentByRecurrenceInterval(IntervalValueObject $interval)
    {
        if ($interval->getIntervalType() === IntervalValueObject::INTERVAL_TYPE_MONTH) {
            return $interval->getIntervalCount();
        }

        return self::MAX_INSTALLMENTS_NUMBER;
    }

    protected function getProductSubscription($productId)
    {
        $productSubscriptionService = new ProductSubscriptionService();
        return $productSubscriptionService->findByProductId($productId);
    }
}