<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractDataService;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;

final class OrderService
{
    /**
     *
     * @param Order $order
     */
    public function syncPlatformWith(Order $order)
    {
        $moneyService = new MoneyService();

        $paidAmount = 0;
        $canceledAmount = 0;
        $refundedAmount = 0;
        foreach ($order->getCharges() as $charge) {
            $paidAmount += $charge->getPaidAmount();
            $canceledAmount += $charge->getCanceledAmount();
            $refundedAmount += $charge->getRefundedAmount();
        }

        $paidAmount = $moneyService->centsToFloat($paidAmount);
        $canceledAmount = $moneyService->centsToFloat($canceledAmount);
        $refundedAmount = $moneyService->centsToFloat($refundedAmount);

        $platformOrder = $order->getPlatformOrder();

        $platformOrder->setTotalPaid($paidAmount);
        $platformOrder->setBaseTotalPaid($paidAmount);
        $platformOrder->setTotalCanceled($canceledAmount);
        $platformOrder->setBaseTotalCanceled($canceledAmount);
        $platformOrder->setTotalRefunded($refundedAmount);
        $platformOrder->setBaseTotalRefunded($refundedAmount);

        $platformOrder->setStatus($order->getStatus());
        //@todo $platformOrder->setState($order->getState());

        $platformOrder->save();
    }

    public function updateAcquirerData(Order $order)
    {
        $dataServiceClass =
            MPSetup::get(MPSetup::CONCRETE_DATA_SERVICE);

        /**
         *
 * @var AbstractDataService $dataService
*/
        $dataService = new $dataServiceClass();

        $dataService->updateAcquirerData($order);
    }

    public function cancelAtMundipagg(Order $order)
    {
        if ($order->getStatus()->equals(OrderStatus::canceled())) {
            return;
        }

        $orderRepository = new OrderRepository();

        $savedOrder = $orderRepository->findByMundipaggId($order->getMundipaggId());
        if ($savedOrder !== null) {
            $order = $savedOrder;
        }

        $APIService = new APIService();

        $charges = $order->getCharges();
        $results = [];
        foreach ($charges as $charge) {
            $result = $APIService->cancelCharge($charge);
            if ($result !== null) {
                $results[$charge->getMundipaggId()->getValue()] = $result;
            }
            $order->updateCharge($charge);
        }

        $orderRepository->save($order);

        if (empty($results)) {
            $i18n = new LocalizationService();
            $order->getPlatformOrder()->addHistoryComment(
                $i18n->getDashboard(
                    "Order '%s' canceled at Mundipagg",
                    $order->getMundipaggId()->getValue()
                )
            );

            $order->getPlatformOrder()->save();
            return;
        }

        $history = '';
    }

    public function cancelAtMundipaggByPlatformOrder(PlatformOrderInterface $platformOrder)
    {
        $orderId = $platformOrder->getMundipaggId();
        $APIService = new APIService();

        $order = $APIService->getOrder($orderId);
        if (is_a($order, Order::class)) {
            $this->cancelAtMundipagg($order);
        }
    }
}