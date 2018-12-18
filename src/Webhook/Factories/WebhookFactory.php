<?php

namespace Mundipagg\Core\Webhook\Factories;

use Mundipagg\Core\Kernel\Aggregates\Order as OrderEntity;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Webhook\ValueObjects\WebhookId;
use Mundipagg\Core\Webhook\ValueObjects\WebhookType;

class WebhookFactory
{
    /**
     * @return Webhook
     */
    public function createFromPostData($postData)
    {
        $webhook = new Webhook();

        $webhook->setId(new WebhookId($postData->id));
        $webhook->setType(WebhookType::fromPostType($postData->type));

        /*
         @todo implement this!
        $entityFactory = MundipaggCore::getFactoryFor($webhook->getType()->getEntityType());
        $entity = $entityFactory->createFromPostData($postData->data);
        */
        //@fixme this is a mock. when the above block was implemented, please remove it.
        $entity = new OrderEntity();

        $webhook->setEntity($entity);

        return $webhook;
    }
}
