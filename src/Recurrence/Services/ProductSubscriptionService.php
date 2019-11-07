<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Recurrence\Factories\ProductSubscriptionFactory;
use Mundipagg\Core\Recurrence\Repositories\ProductSubscriptionRepository;

class ProductSubscriptionService
{
    public function createAtPlatform($formData)
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();
        $productSubscription = $productSubscriptionFactory->createFromPostData($formData);

         $productSubscriptionRepository = new ProductSubscriptionRepository();
         $productSubscriptionRepository->save($productSubscription);
         return $productSubscription;
    }

    public function findById($id)
    {
        $productSubscriptionRepository = new ProductSubscriptionRepository();
        return $productSubscriptionRepository->find($id);
    }
}