<?php

namespace Mundipagg\Core\Webhook\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use MundiPagg\MundiPagg\Model\ChargesRepository;

final class ChargeHandlerService extends AbstractHandlerService
{
    protected function handlePaid(Webhook $webhook)
    {
        $orderRepository = new OrderRepository();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        /** @var Order $order */
        $order = $this->order;

        $charge = $webhook->getEntity();


        $chargeRepository->save($charge);


        $transaction = $charge->getLastTransaction();

        $outdatedCharge = $chargeRepository->findByMundipaggId(
            $charge->getMundipaggId()
        );
        $outdatedCharge->addTransaction($charge->getLastTransaction());
        $charge = $outdatedCharge;

        $paidAmount = $transaction->getAmount();

        $charge->pay($paidAmount);
        $order->updateCharge($charge);

        $history = $this->prepareHistoryComment($charge);

        $orderService->syncPlatformWith($order);
        $this->order->addHistoryComment($history);

        $orderRepository->save($order);

        $returnMessage = $this->prepareReturnMessage($charge);
        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

    //@todo handlePartialCanceled
    protected function handlePartialCanceled_TODO(Webhook $webhook)
    {
        //@todo
    }

    protected function handleRefunded(Webhook $webhook)
    {
        $a = 1;
        $chargeRepository = new ChargeRepository();
        $charge = $webhook->getEntity();


        $chargeRepository->save($charge);

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

    private function prepareHistoryComment(Charge $charge)
    {
        $i18n = new LocalizationService();
        $moneyService = new MoneyService();

        $amountInCurrency = $moneyService->centsToFloat($charge->getPaidAmount());

        $history = $i18n->getDashboard(
            'Payment received: %.2f',
            $amountInCurrency
        );

        $canceledAmount = $charge->getCanceledAmount();
        if ($canceledAmount > 0) {
            $amountCanceledInCurrency = $moneyService->centsToFloat($canceledAmount);

            $history .= " ({$i18n->getDashboard('Partial Payment')}";
            $history .= ". " .
                $i18n->getDashboard(
                    'Canceled amount: %.2f',
                    $amountCanceledInCurrency
                ) . ')';


        }

        return $history;
    }

    private function prepareReturnMessage($charge)
    {
        $moneyService = new MoneyService();

        $amountInCurrency = $moneyService->centsToFloat($charge->getPaidAmount());

        $returnMessage = "Amount Paid: $amountInCurrency";

        $canceledAmount = $charge->getCanceledAmount();
        if ($canceledAmount > 0) {
            $amountCanceledInCurrency = $moneyService->centsToFloat($canceledAmount);

            $returnMessage .= ". Amount Canceled: $amountCanceledInCurrency";
        }

        return $returnMessage;
    }
}