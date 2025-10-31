<?php

namespace Pagarme\Core\Kernel\Log;

use Monolog\LogRecord;
use Pagarme\Core\Kernel\Abstractions\AbstractJsonPrettyFormatter;

class JsonPrettyFormatterWithLogRecord extends AbstractJsonPrettyFormatter
{
    public function format(LogRecord $record): string
    {
        return $this->formatMessage($record);
    }
}
