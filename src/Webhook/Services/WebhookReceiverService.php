<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Webhook\Factories\WebhookFactory;
use Mundipagg\Core\Webhook\Repositories\WebhookRepository;

class WebhookReceiverService
{
    public function handle($postData)
    {
        //@todo log webhook received.

        $repository = new WebhookRepository();
        $webhook = $repository->findByMundipaggId($postData->id);
        if ($webhook !== null) {
            throw new \Exception("Webhoook {$postData->id} already handled!");
        }

        $factory = new WebhookFactory();
        $webhook = $factory->createFromPostData($postData);

        $repository->save($webhook);


        $handlerServiceClass =
            'Mundipagg\\Core\\Webhook\\Services\\' .
            ucfirst($webhook->getType()->getEntityType()).
            'HandlerService';

        if (class_exists($handlerServiceClass)) {
            $handlerService = new $handlerServiceClass();
            return $handlerService->handle($webhook);
        }

        throw new \Exception('Handler for ' .$webhook->getType()->getEntityType() . ' webhook not found!');
    }

}
