<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Factories\ProductSubscriptionFactory;
use Mundipagg\Core\Recurrence\Repositories\ProductSubscriptionRepository;
use Mundipagg\Core\Recurrence\Repositories\RepetitionRepository;

class ProductSubscriptionService
{
    /** @var LogService  */
    protected $logService;

    public function saveProductSubscription(ProductSubscription $productSubscription)
    {
        $this->getLogService()->info("Creating product subscription at platform");
        if (!empty($productSubscription->getId())) {
            $this->deleteRepetitionsBySubscriptionProductId(
                $productSubscription->getId()
            );
        }

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
        if (empty($productSubscription)) {
            throw new \Exception("Subscription Product not found - ID : {$productSubscriptionId} ");
        }
        return $productSubscriptionRepository->delete($productSubscription);
    }

    public function deleteRepetitionsBySubscriptionProductId($subscriptionProductId)
    {
        return $this->getRepetitionsRepository()
            ->deleteBySubscriptionId($subscriptionProductId);
    }

    public function getProductSubscriptionRepository()
    {
        return new ProductSubscriptionRepository();
    }

    public function getProductSubscriptionFactory()
    {
        return new ProductSubscriptionFactory();
    }

    public function getRepetitionsRepository()
    {
        return new RepetitionRepository();
    }

    public function getLogService()
    {
        return new LogService(
            'ProductSubscriptionService',
            true
        );
    }
}