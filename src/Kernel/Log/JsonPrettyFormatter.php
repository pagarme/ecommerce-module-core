<?php

namespace Pagarme\Core\Kernel\Log;

use Pagarme\Core\Kernel\Abstractions\AbstractJsonPrettyFormatter;
class JsonPrettyFormatter extends AbstractJsonPrettyFormatter
{
    public function format(array $record): string
    {
        return $this->formatMessage($record);
    }
}
