<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Exceptions\InvalidClassException;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;

final class FactoryService
{
    /**
     *
     * @param $component
     * @param $entity
     *
     * @return FactoryInterface
     */
    public function getFactoryFor($component, $entity)
    {
        $entityFactory = ucfirst($entity) . "Factory";
        $fullFactoryClassName = 'Mundipagg\\Core\\' . $component . '\\Factories\\' . $entityFactory;

        try {
            if (class_exists($fullFactoryClassName)) {
                $factory = new $fullFactoryClassName;
                if (is_a($factory, FactoryInterface::class)) {
                    return $factory;
                }
            }
        }catch(\Exception $e) {
            throw new InvalidClassException($fullFactoryClassName, FactoryInterface::class);
        }
        throw new InvalidClassException($fullFactoryClassName, FactoryInterface::class);
    }
}