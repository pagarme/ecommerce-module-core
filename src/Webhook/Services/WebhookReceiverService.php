<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Exceptions\AbstractMundipaggCoreException;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Webhook\Exceptions\WebhookAlreadyHandledException;
use Mundipagg\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Mundipagg\Core\Webhook\Factories\WebhookFactory;
use Mundipagg\Core\Webhook\Repositories\WebhookRepository;
use Mundipagg\Core\Webhook\ValueObjects\WebhookId;

class WebhookReceiverService
{
    /**
     *
     * @param  $postData
     * @return mixed
     * @throws NotFoundException
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidClassException
     */
    public function handle($postData)
    {
        $logService = new LogService(
            'Webhook',
            true
        );
        try {
            $logService->info("Received", $postData);

            $repository = new WebhookRepository();
            $webhook = $repository->findByMundipaggId(new WebhookId($postData->id));
            if ($webhook !== null) {
                throw new WebhookAlreadyHandledException($webhook);
            }

            $factory = new WebhookFactory();
            $webhook = $factory->createFromPostData($postData);

            $handlerService = $this->getHandlerServiceFor($webhook);

            $return = $handlerService->handle($webhook);
            $repository->save($webhook);
            $logService->info(
                "Webhook handled successfuly",
                (object)[
                    'id' => $webhook->getId(),
                    'mundipaggId' => $webhook->getMundipaggId(),
                    'result' => $return
                ]
            );

            return $return;
        }catch(AbstractMundipaggCoreException $e) {
            $logService->exception($e);
            throw $e;
        }
    }

    private function getHandlerServiceFor(Webhook $webhook)
    {
        $handlerServiceClass =
            'Mundipagg\\Core\\Webhook\\Services\\' .
            ucfirst($webhook->getType()->getEntityType()).
            'HandlerService';

        if (!class_exists($handlerServiceClass)) {
            throw new WebhookHandlerNotFoundException($webhook);
        }

        /**
         *
         * @var AbstractHandlerService $handlerService
         */
        return new $handlerServiceClass();
    }
}
