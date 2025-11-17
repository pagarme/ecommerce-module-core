<?php

namespace Pagarme\Core\Kernel\Factories;

use Pagarme\Core\Kernel\Log\JsonPrettyFormatter;
use Pagarme\Core\Kernel\Log\JsonPrettyFormatterWithLogRecord;

class JsonPrettyFormatterFactory
{
    public static function create()
    {
        // Verify if Monolog\LogRecord class exists (Monolog v3+)
        if (class_exists("\\Monolog\\LogRecord")) {
            return new JsonPrettyFormatterWithLogRecord();
        }
        return new JsonPrettyFormatter();
    }
}
