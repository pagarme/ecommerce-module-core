<?php

namespace Mundipagg\Core\Webhook\Factories;

use Mundipagg\Core\Kernel\Exceptions\InvalidClassException;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\Services\FactoryService;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Webhook\ValueObjects\WebhookId;
use Mundipagg\Core\Webhook\ValueObjects\WebhookType;

class WebhookFactory implements FactoryInterface
{
    /**
     * @param $postData
     * @return Webhook
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidClassException
     */
    public function createFromPostData($postData)
    {
        $webhook = new Webhook();

        $webhook->setMundipaggId(new WebhookId($postData->id));
        $webhook->setType(WebhookType::fromPostType($postData->type));

        $factoryService = new FactoryService;

        try {
            $entityFactory =
                $factoryService->getFactoryFor(
                    'Kernel',
                    $webhook->getType()->getEntityType()
                );
        }catch(InvalidClassException $e) {
            throw new NotFoundException("Handler not found for {$postData->type} webhook");
        }

        $entity = $entityFactory->createFromPostData($postData->data);

        //$entity = new OrderEntity();

        $webhook->setEntity($entity);

        return $webhook;
    }

    /**
     * @param $dbData
     * @return Webhook
     */
    public function createFromDbData($dbData)
    {
        $webhook = new Webhook();

        $webhook->setId($dbData['id']);
        $webhook->setMundipaggId(new WebhookId($dbData['mundipagg_id']));

        return $webhook;
    }
}
