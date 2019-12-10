<?php


namespace Mundipagg\Core\Recurrence\Services;


use Mundipagg\Core\Recurrence\Repositories\SubscriptionRepository;

class SubscriptionService
{
    public function listAll()
    {
        return $this->getSubscriptionRepository()
            ->listEntities(0, false);
    }

    public function getSubscriptionRepository()
    {
        return new SubscriptionRepository();
    }
}