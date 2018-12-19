<?php

namespace Mundipagg\Core\Webhook\Services;


use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

abstract class AbstractHandlerService
{
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
            return $this->$handler($webhook);
        }
    }

    /** @return string */
    protected function getValidEntity()
    {
        $childClassName = substr(strrchr(static::class, "\\"), 1);
        $childEntity = str_replace('HandlerService','',$childClassName);
        return strtolower($childEntity);
    }
}