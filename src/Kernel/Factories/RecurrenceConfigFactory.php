<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;

class RecurrenceConfigFactory implements FactoryCreateFromDbDataInterface
{
    /**
     * @param array $data
     * @return AbstractEntity|RecurrenceConfig
     */
    public function createFromDbData($data)
    {
        if (!isset($data->enabled)) {
            $data->enabled = false;
        }

        if (!isset($data->checkoutConflictMessage)) {
            $data->checkoutConflictMessage = '';
        }

        if (!isset($data->showRecurrenceCurrencyWidget)) {
            $data->showRecurrenceCurrencyWidget = false;
        }

        return new RecurrenceConfig(
            $data->enabled,
            $data->checkoutConflictMessage,
            $data->showRecurrenceCurrencyWidget
        );
    }
}
