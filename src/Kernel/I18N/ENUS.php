<?php

namespace Mundipagg\Core\Kernel\I18N;

use Mundipagg\Core\Kernel\Abstractions\AbstractI18NTable;

class ENUS extends AbstractI18NTable
{
    protected function getTable()
    {
        return [
            'Invoice created: #%d.' => null,
            'Webhook received: %s.%s' => null,
            'Order paid.' => null,
            'Order canceled.' => null,
            'Payment received: %.2f' => null,
            'Canceled amount: %.2f' => null,
            'Refunded amount: %.2f' => null,
            'Partial Payment' => null,
            'Charge canceled.' => null
        ];
    }
}