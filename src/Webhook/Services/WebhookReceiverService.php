<?php

namespace Pagarme\Core\Webhook\Services;

use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Kernel\Exceptions\NotFoundException;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Webhook\Exceptions\WebhookAlreadyHandledException;
use Pagarme\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Pagarme\Core\Webhook\Factories\WebhookFactory;
use Pagarme\Core\Webhook\Repositories\WebhookRepository;
use Pagarme\Core\Webhook\ValueObjects\WebhookId;

class WebhookReceiverService
{
    /**
     *
     * @param  $postData
     * @return mixed
     * @throws NotFoundException
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidClassException
     */
    public function handle($postData)
    {
        $logService = new LogService(
            'Webhook',
            true
        );
        try {
            $logService->info("Received", $this->prepareToLog($postData));

            $repository = new WebhookRepository();
            $webhook = $repository->findByPagarmeId(new WebhookId($postData->id));
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
                    'pagarmeId' => $webhook->getPagarmeId(),
                    'result' => $return
                ]
            );

            return $return;
        } catch(AbstractPagarmeCoreException $e) {
            $logService->exception($e);
            throw $e;
        }
    }

    private function getHandlerServiceFor(Webhook $webhook)
    {
        $handlerServiceClass =
            'Pagarme\\Core\\Webhook\\Services\\' .
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

    private function prepareToLog($postData)
    {
        $postData->data['customer']['name'] = preg_replace('/^.{8}/', '$1**', $postData->data['customer']['name']);
        $postData->data['customer']['email'] = preg_replace('/(?<=.).(?=.*@)/','*', $postData->data['customer']['email']);
        $postData->data['customer']['phones'] = null;
        $postData->data['customer']['address']['street'] = preg_replace('/^.{8}/', '$1**', $postData->data['customer']['address']['street']);
        $postData->data['customer']['address']['line_1'] = preg_replace('/^.{8}/', '$1**', $postData->data['customer']['address']['line_1']);
        $postData->data['customer']['address']['line_2'] = null;
        $postData->data['customer']['address']['number'] = null;
        $postData->data['customer']['address']['complement'] = null;
        $postData->data['customer']['address']['zip_code'] = preg_replace('/^.{5}/', '$1**', $postData->data['customer']['address']['zip_code']);
        $postData->data['customer']['address']['neighborhood'] = null;
        // Charges
        if (array_key_exists('charges', $postData->data) && is_array($postData->data['charges'])) {
            $charges = [];
            foreach ($postData->data['charges'] as $charge) {
                $charge['last_transaction']['card']['id'] = preg_replace('/^(.*?).{8}(.{3})$/', '$1********$2', $charge['last_transaction']['card']['id']);
                $charge['last_transaction']['card']['holder_name'] = preg_replace('/^.{8}/', '$1**', $charge['last_transaction']['card']['holder_name']);
                $charge['last_transaction']['card']['billing_address']['street'] = preg_replace('/^.{8}/', '$1**', $charge['last_transaction']['card']['billing_address']['street']);
                $charge['last_transaction']['card']['billing_address']['line_1'] = preg_replace('/^.{8}/', '$1**', $charge['last_transaction']['card']['billing_address']['line_1']);
                $charge['last_transaction']['card']['billing_address']['line_2'] = null;
                $charge['last_transaction']['card']['billing_address']['number'] = null;
                $charge['last_transaction']['card']['billing_address']['complement'] = null;
                $charge['last_transaction']['card']['billing_address']['zip_code'] = preg_replace('/^.{5}/', '$1**', $charge['last_transaction']['card'][ 'billing_address']['zip_code']);
                $charge['last_transaction']['card']['billing_address']['neighborhood'] = null;

                $charge['last_transaction']['card']['customer']['name'] = preg_replace('/^.{8}/', '$1**', $charge['last_transaction']['card'][ 'customer']['name']);
                $charge['last_transaction']['card']['customer']['email'] = preg_replace('/^.{3}\K|.(?=.*@)/img','*', $charge['last_transaction']['card'][ 'customer']['email']);
                $charge['last_transaction']['card']['customer']['phones'] = null;
                $charge['last_transaction']['card']['customer']['address']['street'] = preg_replace('/^.{8}/', '$1**', $charge['last_transaction']['card'][ 'customer']['address']['street']);
                $charge['last_transaction']['card']['customer']['address']['line_1'] = preg_replace('/^.{8}/', '$1**', $charge['last_transaction']['card'][ 'customer']['address']['line_1']);
                $charge['last_transaction']['card']['customer']['address']['line_2'] = null;
                $charge['last_transaction']['card']['customer']['address']['number'] = null;
                $charge['last_transaction']['card']['customer']['address']['complement'] = null;
                $charge['last_transaction']['card']['customer']['address']['zip_code'] = preg_replace('/^.{5}/', '$1**', $charge['last_transaction']['card'][ 'customer']['address']['zip_code']);
                $charge['last_transaction']['card']['customer']['address']['neighborhood'] = null;
                $charge['last_transaction']['card']['customer']['phones'] = null;

                $charge['customer']['name'] = preg_replace('/^.{8}/', '$1**', $charge['last_transaction']['card'][ 'customer']['name']);
                $charge['customer']['email'] = preg_replace('/^.{3}\K|.(?=.*@)/img','*', $charge['last_transaction']['card'][ 'customer']['email']);
                $charge['customer']['phones'] = null;
                $charge['customer']['address']['street'] = preg_replace('/^.{8}/', '$1**', $charge['last_transaction']['card'][ 'customer']['address']['street']);
                $charge['customer']['address']['line_1'] = preg_replace('/^.{8}/', '$1**', $charge['last_transaction']['card'][ 'customer']['address']['line_1']);
                $charge['customer']['address']['line_2'] = null;
                $charge['customer']['address']['number'] = null;
                $charge['customer']['address']['complement'] = null;
                $charge['customer']['address']['zip_code'] = preg_replace('/^.{5}/', '$1**', $charge['last_transaction']['card'][ 'customer']['address']['zip_code']);
                $charge['customer']['address']['neighborhood'] = null;
                $charge['customer']['phones'] = null;
                $charges[] = $charge;
            }
            $postData->data['charges'] = $charges;
        }
        $postData->data['shipping']['recipient_name'] = preg_replace('/^.{8}/', '$1**', $postData->data['customer']['name']);
        $postData->data['shipping']['recipient_phone'] = null;
        $postData->data['shipping']['phones'] = null;
        $postData->data['shipping']['address']['street'] = preg_replace('/^.{8}/', '$1**', $postData->data['customer']['address']['street']);
        $postData->data['shipping']['address']['line_1'] = preg_replace('/^.{8}/', '$1**', $postData->data['customer']['address']['line_1']);
        $postData->data['shipping']['address']['line_2'] = null;
        $postData->data['shipping']['address']['number'] = null;
        $postData->data['shipping']['address']['complement'] = null;
        $postData->data['shipping']['address']['zip_code'] = preg_replace('/^.{5}/', '$1**', $postData->data['customer']['address']['zip_code']);
        $postData->data['shipping']['address']['neighborhood'] = null;
        return $postData;
    }
}
