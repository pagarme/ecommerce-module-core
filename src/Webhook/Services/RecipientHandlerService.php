<?php
namespace Pagarme\Core\Webhook\Services;

use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Kernel\Exceptions\NotFoundException;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Pagarme\Core\Marketplace\Services\RecipientService;
use Pagarme\Core\Marketplace\Aggregates\Recipient;

class RecipientHandlerService
{
    /**
     * @param Webhook $webhook
     * @return mixed
     * @throws InvalidParamException
     * @throws NotFoundException
     * @throws WebhookHandlerNotFoundException
     */
    public function handle(Webhook $webhook)
    {
        $handler = $this->getActionHandle($webhook->getType()->getAction());

        if (method_exists($this, $handler)) {
            return $this->$handler($webhook);
        }

        $type = "{$webhook->getType()->getEntityType()}.{$webhook->getType()->getAction()}";
        $message = "Webhook {$type} not implemented";
        $this->getLogService()->info($message);

        return [
            "message" => $message,
            "code" => 200
        ];
    }

    protected function handleUpdated(Webhook $webhook)
    {
        $recipientService = new RecipientService();
        /** @var Recipient $recipientEntity */
        $recipientEntity = $webhook->getEntity();
        $foundedRecipent = $recipientService->findSavedByPagarmeId($recipientEntity->getPagarmeId());
        if (empty($foundedRecipent)) {
            $message = "Recipient with id {$recipientEntity->getPagarmeId()} not founded in the platform";
            $this->getLogService()->info($message);
            return [
                "message" => $message,
                "code" => 200
            ];
        }

        $foundedRecipent->setStatus($recipientEntity->getStatus());

        $recipientService->saveRecipient($foundedRecipent);

        return [
            "message" => 'Recipient updated',
            "code" => 200
        ];
    }

    protected function getActionHandle($action)
    {
        $baseActions = explode('_', $action ?? '');
        $action = '';
        foreach ($baseActions as $baseAction) {
            $action .= ucfirst($baseAction);
        }

        return 'handle' . $action;
    }

    protected function getLogService()
    {
        return new LogService('Webhook', true);
    }
}