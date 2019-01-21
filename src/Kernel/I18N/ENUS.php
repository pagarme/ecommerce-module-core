<?php

namespace Mundipagg\Core\Kernel\I18N;

use Mundipagg\Core\Kernel\Abstractions\AbstractI18NTable;

class ENUS extends AbstractI18NTable
{
    protected function getTable()
    {
        return [
            'Invoice created: #%s.' => null,
            'Invoice canceled: #%s.' => null,
            'Webhook received: %s.%s' => null,
            'Order paid.' => null,
            'Order canceled.' => null,
            'Payment received: %.2f' => null,
            'Canceled amount: %.2f' => null,
            'Refunded amount: %.2f' => null,
            'Partial Payment' => null,
            'Charge canceled.' => null,
            'Creditmemo created: #%s.' => null,
            'until now' => null,
            'Extra amount paid: %.2f' => null,
            "Order '%s' canceled at Mundipagg" => null,
            'Remaining amount: %.2f' => null,
            "Some charges couldn't be canceled at Mundipagg. Reasons:" => null,
            "without interest" => null,
            "with %.2f%% of interest" => null,
            "%dx of %s %s (Total: %s)" => null
        ];
    }
}