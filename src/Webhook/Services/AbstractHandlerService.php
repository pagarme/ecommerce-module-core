<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;

abstract class AbstractHandlerService
{
    /**
     * @var Order
     */
    protected $order;

    public function getActionHandle($action)
    {
        $baseActions = explode('_', $action);
        $action = '';
        foreach ($baseActions as $baseAction) {
            $action .= ucfirst($baseAction);
        }

        return 'handle' . $action;
    }

    public function validateWebhookHandling($entityType)
    {
        $validEntity = $this->getValidEntity();
        if ($entityType !== $validEntity) {
            throw new InvalidParamException(
                self::class . ' only supports ' . $validEntity . ' type webhook handling!',
                $entityType . '.(action)'
            );
        }
    }

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
            $this->loadOrder($webhook);
            $platformOrder = $this->order->getPlatformOrder();

            if ($platformOrder->getIncrementId() !== null) {
                $this->addWebHookReceivedHistory($webhook);
                $platformOrder->save();
                return $this->$handler($webhook);
            }

            throw new NotFoundException("Order #{$webhook->getEntity()->getCode()} not found.");
        }

        throw new WebhookHandlerNotFoundException($webhook);
    }

    /**
     *
     * @return string
     */
    protected function getValidEntity()
    {
        $childClassName = substr(strrchr(static::class, "\\"), 1);
        $childEntity = str_replace('HandlerService', '', $childClassName);
        return strtolower($childEntity);
    }

    protected function addWebHookReceivedHistory(Webhook $webhook)
    {
        $i18n = new LocalizationService();
        $message = $i18n->getDashboard(
            'Webhook received: %s.%s',
            $webhook->getType()->getEntityType(),
            $webhook->getType()->getAction()
        );

        $platformOrder = $this->order->getPlatformOrder();
        $platformOrder->addHistoryComment($message);
    }

    abstract protected function loadOrder(Webhook $webhook);
}