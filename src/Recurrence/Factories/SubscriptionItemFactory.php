<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Recurrence\Aggregates\SubscriptionItem;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionItemId;

class SubscriptionItemFactory implements FactoryInterface
{
    /**
     * @param array $postData
     * @return AbstractEntity|Subscription
     * @throws InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $subscriptionItem = new SubscriptionItem();

        $subscriptionItem->setSubscriptionId(new SubscriptionId($postData['subscription_id']));
        $subscriptionItem->setMundipaggId(new SubscriptionItemId($postData['id']));
        $subscriptionItem->setCode($postData['code']);
        $subscriptionItem->setQuantity($postData['quantity']);

        return $subscriptionItem;
    }
    /**
     * @param array $dbData
     * @return AbstractEntity|Subscription
     * @throws InvalidParamException
     */
    public function createFromDbData($dbData)
    {
        $subscriptionItem = new SubscriptionItem();

        $subscriptionItem->setSubscriptionId(new SubscriptionId($dbData['id']));
        $subscriptionItem->setMundipaggId(new SubscriptionItemId($dbData['id']));
        $subscriptionItem->setCode($dbData['code']);
        $subscriptionItem->setQuantity($dbData['quantity']);

        return $subscriptionItem;
    }
}
