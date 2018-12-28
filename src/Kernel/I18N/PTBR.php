<?php

namespace Mundipagg\Core\Kernel\I18N;

use Mundipagg\Core\Kernel\Abstractions\AbstractI18NTable;

class PTBR extends AbstractI18NTable
{
    protected function getTable()
    {
        return [
            'Invoice created: #%d.' => 'Invoice criada: #%d',
            'Webhook received: %s.%s' => 'Webhook recebido: %s.%s',
            'Order paid.' => 'Pedido pago.',
            'Payment received: %.2f' => 'Pagamento recebido: %.2f',
            'Canceled amount: %.2f' => 'Quantia cancelada: %.2f',
            'Refunded amount: %.2f' => 'Quantia estornada: %.2f',
            'Partial Payment' => 'Pagamento Parcial'
        ];
    }
}