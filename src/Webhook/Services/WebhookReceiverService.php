<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Mundipagg\Core\Webhook\Factories\WebhookFactory;
use Mundipagg\Core\Webhook\Repositories\WebhookRepository;

class WebhookReceiverService
{
    /**
     * @param $postData
     * @return mixed
     * @throws NotFoundException
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidClassException
     */
    public function handle($postData)
    {
        try {
            //@todo log webhook received.

            $repository = new WebhookRepository();
            $webhook = $repository->findByMundipaggId($postData->id);
            if ($webhook !== null) {
                throw new \Exception("Webhoook {$postData->id} already handled!");
            }

            $factory = new WebhookFactory();
            $webhook = $factory->createFromPostData($postData);

            $handlerServiceClass =
                'Mundipagg\\Core\\Webhook\\Services\\' .
                ucfirst($webhook->getType()->getEntityType()).
                'HandlerService';

            if (!class_exists($handlerServiceClass)) {
                throw new WebhookHandlerNotFoundException($webhook);
            }
            /**
             * @var AbstractHandlerService $handlerService
             */
            $handlerService = new $handlerServiceClass();

            $return = $handlerService->handle($webhook);
            $repository->save($webhook);

            return $return;

        }catch(NotFoundException $e) {
            //@todo log invalid webhook type.
            throw $e;
        }
    }

}
