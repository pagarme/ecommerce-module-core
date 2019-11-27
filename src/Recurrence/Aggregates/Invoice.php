<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;

class Invoice extends AbstractEntity
{
    /**
     * @var SubscriptionId
     */
    private $subscriptionId;

    /**
     * @return SubscriptionId
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @param  SubscriptionId $subscriptionId
     * @return $this
     */
    public function setSubscriptionId(SubscriptionId $subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'subscriptionId' => $this->getSubscriptionId()
        ];
    }
}
