<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Services\InvoiceService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

final class ChargeHandlerService extends AbstractHandlerService
{
    protected function handlePaid(Webhook $webhook)
    {
        $i18n = new LocalizationService();

        /** @var Charge $charge */
        $charge = $webhook->getEntity();
        $paidAmount = $charge->getPaidAmount();

        $amountInCurrency = $this->order->payAmount($paidAmount);

        $this->order->addHistoryComment(
            $i18n->getDashboard(
                'Payment received: %.2f',
                $amountInCurrency
            )
        );

        $this->order->save();

        $result = [
            "message" => "Amount Paid: $amountInCurrency",
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