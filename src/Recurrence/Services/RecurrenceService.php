<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Interfaces\PlatformProductInterface;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\Repositories\SubscriptionItemRepository;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionItemId;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

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

        $productPlan = $this->getProductPlan($productId);
        if ($productPlan !== null) {
            return $productPlan;
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

    protected function getProductPlan($productId)
    {
        $productSubscriptionService = new PlanService();
        return $productSubscriptionService->findByProductId($productId);
    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    public function getSubProductByNameAndRecurrenceType($productName, $subscription)
    {
        $recurrenceType = $subscription->getRecurrenceType();

        if ($recurrenceType === Plan::RECURRENCE_TYPE) {
            $plan = (new PlanService)->findByMundipaggId(
                $subscription->getPlanId()
            );

            return $this->getProductByName($productName, $plan);
        }
    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    public function getSubscriptionItemByProductId($subscriptionItemId)
    {
        $subscriptionItemRepository = new SubscriptionItemRepository();
        return $subscriptionItemRepository->findByMundipaggId(
            new SubscriptionItemId($subscriptionItemId)
        );

    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    public function getProductByName($productName, $recurrence)
    {
        foreach ($recurrence->getItems() as $item) {
            $product = $this->getProductDecorated($item->getProductId());
            $subProduct = new SubProduct();
            $subProduct->setName($product->getName());
            if ($productName == $subProduct->getName()) {
                return $item;
            }
            continue;
        }
    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    public function getProductDecorated($id)
    {
        $productDecorator =
            Magento2CoreSetup::get(
                Magento2CoreSetup::CONCRETE_PRODUCT_DECORATOR_CLASS
            );

        /**
         * @var PlatformProductInterface $product
         */
        $product = new $productDecorator();
        $product->loadByEntityId($id);

        return $product;
    }
}