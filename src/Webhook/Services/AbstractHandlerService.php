<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;

abstract class AbstractHandlerService
{
    /**
     *
     * @var PlatformOrderInterface 
     */
    protected $order;

    /**
     *
     * @param  Webhook $webhook
     * @return mixed
     * @throws InvalidParamException
     * @throws NotFoundException
     */
    public function handle(Webhook $webhook)
    {
        $entityType = $webhook->getType()->getEntityType();
        $validEntity = $this->getValidEntity();
        if ($entityType !== $validEntity) {
            throw new InvalidParamException(
                self::class . ' only supports '. $validEntity .' type webhook handling!',
                $entityType . '.(action)'
            );
        }

        $handler = 'handle' . ucfirst($webhook->getType()->getAction());
        if (method_exists($this, $handler)) {
            $this->loadOrder($webhook);

            if ($this->order->getIncrementId() !== null) {
                $this->addWebHookReceivedHistory($webhook);
                $this->order->save();
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

        $this->order->addHistoryComment($message);
    }

    abstract protected function loadOrder($webhook);
}