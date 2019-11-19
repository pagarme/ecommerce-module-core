<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class RecurrenceService
{
    const MAX_INSTALLMENTS_NUMBER = 12;

    public function getRecurrenceProductByProductId($productId)
    {
        $productSubscription = $this->getProductSubscription($productId);
        if ($productSubscription !== null) {
            return $productSubscription;
        }

        $plan = $this->getPlan($productId);
        if ($plan !== null) {
            return $plan;
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

    protected function getPlan($productId)
    {
        $planService = new PlanService();
        return $planService->findByProductId($productId);
    }

    protected function getProductSubscription($productId)
    {
        $productSubscriptionService = new ProductSubscriptionService();
        return $productSubscriptionService->findByProductId($productId);
    }
}