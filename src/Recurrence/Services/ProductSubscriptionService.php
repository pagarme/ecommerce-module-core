<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Recurrence\Factories\ProductSubscriptionFactory;

class ProductSubscriptionService
{
    public function saveProduct($formData, $productId = null)
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();
        if (!empty($productId)) {
            $formData['id'] = $productId;
        }
        $productSubscription = $productSubscriptionFactory->createFromPostData($formData);

//         $productSubscriptionRepository = new ProductSubscriptionRepository();
//         $productSubscriptionRepository->save($productSubscription);
         return $productSubscription;
    }
}