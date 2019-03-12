<?php

namespace Mundipagg\Core\Kernel\I18N;

use Mundipagg\Core\Kernel\Abstractions\AbstractI18NTable;

class PTBR extends AbstractI18NTable
{
    protected function getTable()
    {
        return [
            'Invoice created: #%s.' => 'Invoice criada: #%s',
            'Invoice canceled: #%s.' => 'Invoice cancelada: #%s',
            'Webhook received: %s.%s' => 'Webhook recebido: %s.%s',
            'Order paid.' => 'Pedido pago.',
            'Order created at Mundipagg. Id: %s' => 'Pedido criado na Mundipagg. Id %s',
            'Order waiting for online retries at Mundipagg.' => 'Pedido aguardando por retentativas online na Mundipagg.',
            'Order canceled.' => 'Pedido cancelado.',
            'Payment received: %.2f' => 'Pagamento recebido: %.2f',
            'Canceled amount: %.2f' => 'Quantia cancelada: %.2f',
            'Refunded amount: %.2f' => 'Quantia estornada: %.2f',
            'Partial Payment' => 'Pagamento Parcial',
            'Charge canceled.' => 'Cobrança cancelada.',
            'Creditmemo created: #%s.' => 'Creditmemo criado: #%s.',
            'until now' => 'até agora',
            'Extra amount paid: %.2f' => "Quantia extra paga: %.2f",
            "Order '%s' canceled at Mundipagg" => "Pedido '%s' cancelado na Mundipagg",
            'Remaining amount: %.2f' => "Quantidade faltante: %.2f",
            "Some charges couldn't be canceled at Mundipagg. Reasons:" => "Algumas cobranças não puderam ser canceladas na Mundipagg. Razões:",
            "without interest" => "sem juros",
            "with %.2f%% of interest" => "com %.2f%% de juros",
            "%dx of %s %s (Total: %s)" => "%dx de %s %s (Total: %s)",
            "Order payment failed" => "Pagamento do pedido falhou",
            "The order will be canceled" => "O pedido será cancelado"
        ];
    }
}