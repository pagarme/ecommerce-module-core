<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Factories\ProductSubscriptionFactory;
use Mundipagg\Core\Recurrence\Repositories\ProductSubscriptionRepository;

class ProductSubscriptionService
{
    /** @var LogService  */
    protected $logService;

    public function saveProductSubscription(ProductSubscription $productSubscription)
    {
        $this->getLogService()->info("Creating product subscription at platform");

        $productSubscriptionRepository = $this->getProductSubscriptionRepository();
        $productSubscriptionRepository->save($productSubscription);
        $this->getLogService()->info("Subscription created: " . $productSubscription->getId());

        return $productSubscription;
    }

    public function findById($id)
    {
        $productSubscriptionRepository = $this->getProductSubscriptionRepository();
        return $productSubscriptionRepository->find($id);
    }

    public function findAll()
    {
        return $this->getProductSubscriptionRepository()
            ->listEntities(0, false);
    }

    public function findByProductId($id)
    {
        $productSubscriptionRepository = $this->getProductSubscriptionRepository();
        return $productSubscriptionRepository->findByProductId($id);
    }

    public function delete($productSubscriptionId)
    {
        $productSubscriptionRepository = $this->getProductSubscriptionRepository();
        $productSubscription = $productSubscriptionRepository->find($productSubscriptionId);
        return $productSubscriptionRepository->delete($productSubscription);
    }

    public function getProductSubscriptionRepository()
    {
        return new ProductSubscriptionRepository();
    }

    public function getProductSubscriptionFactory()
    {
        return new ProductSubscriptionFactory();
    }

    public function getLogService()
    {
        return new LogService(
            'ProductSubscriptionService',
            true
        );
    }
}