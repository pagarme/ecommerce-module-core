<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\ValueObjects\TransactionStatus;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

final class ChargeHandlerService extends AbstractHandlerService
{
    protected function handlePaid(Webhook $webhook)
    {
        $i18n = new LocalizationService();

        /**
         *
         * @var Charge $charge
        */
        $charge = $webhook->getEntity();
        $transaction = $charge->getLastTransaction();
        $paidAmount = $transaction->getAmount();

        $amountInCurrency = $this->order->payAmount($paidAmount);

        $history = $i18n->getDashboard(
            'Payment received: %.2f',
            $amountInCurrency
        );

        $returnMessage = "Amount Paid: $amountInCurrency";

        if ($transaction->getStatus()->equals(TransactionStatus::partialCapture())) {
            $history .= " ({$i18n->getDashboard('Partial Payment')}";

            $totalAmount = $charge->getAmount();
            $amountToCancel = $totalAmount - $paidAmount;

            $amountCanceledInCurrency = $this->order->cancelAmount($amountToCancel);
            $history .= ". " .
                $i18n->getDashboard(
                    'Canceled amount: %.2f',
                    $amountCanceledInCurrency
                ) . ')';

            $returnMessage .= ". Amount Canceled: $amountCanceledInCurrency";
        }

        $this->order->addHistoryComment(
            $history
        );

        $this->order->save();

        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

    protected function loadOrder($webhook)
    {
        $orderDecoratorClass =
            MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

        /**
         *
         * @var PlatformOrderInterface $order
        */
        $order = new $orderDecoratorClass();
        $order->loadByIncrementId($webhook->getEntity()->getCode());
        $this->order = $order;
    }
}