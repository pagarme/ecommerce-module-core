<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Recurrence\Factories\ProductSubscriptionFactory;
use Mundipagg\Core\Recurrence\Repositories\ProductSubscriptionRepository;

class ProductSubscriptionService
{
    /** @var LogService  */
    protected $logService;

    public function __construct()
    {
        $this->logService = new LogService(
            'ProductSubscriptionService',
            true
        );
    }

    public function createAtPlatform($formData)
    {
        $this->logService->info("Creating product subscription at platform");
        $productSubscriptionFactory = new ProductSubscriptionFactory();
        $productSubscription = $productSubscriptionFactory->createFromPostData($formData);

        $productSubscriptionRepository = new ProductSubscriptionRepository();
        $productSubscriptionRepository->save($productSubscription);
        $this->logService->info("Subscription created: " . $productSubscription->getId());

        return $productSubscription;
    }

    public function findById($id)
    {
        $productSubscriptionRepository = new ProductSubscriptionRepository();
        return $productSubscriptionRepository->find($id);
    }
}