<?php

namespace Mundipagg\Core\Kernel\Services;

use MundiAPILib\Models\GetChargeResponse;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Responses\ServiceResponse;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Payment\Services\ResponseHandlers\OrderHandler;
use Mundipagg\Core\Webhook\Services\ChargeHandlerService;

class ChargeService
{

    protected $charge;

    protected $logService;

    protected $order;

    public function __construct(Charge $charge)
    {
        $this->charge = $charge;

        $this->order = (new OrderRepository)->findByMundipaggId(
            new OrderId($this->charge->getOrderId()->getValue())
        );
        $this->logService = new LogService(
            'ChargeService',
            true
        );
    }

    public function capture($amount = 0)
    {
        $this->logService->info("Charge capture");
        $orderRepository = new OrderRepository();
        $orderService = new OrderService();
        $chargeHandlerService = new ChargeHandlerService();

        $platformOrder = $this->order->getPlatformOrder();

        $apiService = new APIService();
        $this->logService->info(
            "Capturing charge on Mundipagg - " . $this->charge->getMundipaggId()->getValue()
        );

        $resultApi = $apiService->captureCharge($this->charge, $amount);

        if ($resultApi instanceof GetChargeResponse) {

            if (!$this->charge->getStatus()->equals(ChargeStatus::paid())) {
                $this->logService->info(
                    "Pay charge - " . $this->charge->getMundipaggId()->getValue()
                );
                $this->charge->pay($amount);
            }

            if ($this->charge->getPaidAmount() == 0) {
                $this->charge->setPaidAmount($amount);
            }

            $this->logService->info("Update Charge on Order");
            $this->order->updateCharge($this->charge);
            $orderRepository->save($this->order);

            $this->logService->info("Adding history on Order");
            $history = $chargeHandlerService->prepareHistoryComment($this->charge);
            $platformOrder->addHistoryComment($history);

            $this->logService->info("Synchronizing with platform Order");
            $orderService->syncPlatformWith($this->order);

            $this->logService->info("Change Order status");
            $this->order->setStatus(OrderStatus::paid());
            $orderHandlerService = new OrderHandler();
            $orderHandlerService->handle($this->order);

            $message = $chargeHandlerService->prepareReturnMessage($this->charge);

            return new ServiceResponse(true, $message);
        }

        return new ServiceResponse(false, $resultApi);
    }

    public function cancel($amount = 0)
    {
        $this->logService->info("Charge cancel");

        $orderRepository = new OrderRepository();
        $orderService = new OrderService();
        $moneyService = new MoneyService();
        $chargeHandlerService = new ChargeHandlerService();
        $i18n = new LocalizationService();

        $platformOrder = $this->order->getPlatformOrder();

        $apiService = new APIService();
        $this->logService->info(
            "Cancel charge on Mundipagg - " . $this->charge->getMundipaggId()->getValue()
        );

        $resultApi = $apiService->cancelCharge($this->charge, $amount);

        if ($resultApi === null) {

            $this->order->updateCharge($this->charge);

            $orderRepository->save($this->order);
            $history = $chargeHandlerService->prepareHistoryComment($this->charge);

            $this->order->getPlatformOrder()->addHistoryComment($history);
            $orderService->syncPlatformWith($this->order);

            $platformOrderGrandTotal = $moneyService->floatToCents(
                $platformOrder->getGrandTotal()
            );
            $platformOrderTotalCanceled = $moneyService->floatToCents(
                $platformOrder->getTotalCanceled()
            );

            $platformOrderTotalRefunded = $moneyService->floatToCents(
                $platformOrder->getTotalRefunded()
            );
            if (
                $platformOrderGrandTotal === $platformOrderTotalCanceled ||
                $platformOrderGrandTotal === $platformOrderTotalRefunded
            ) {
                $this->logService->info("Change Order status");

                $this->order->setStatus(OrderStatus::canceled());
                $this->order->getPlatformOrder()->setState(OrderState::canceled());
                $this->order->getPlatformOrder()->save();

                $this->order->getPlatformOrder()->addHistoryComment(
                    $i18n->getDashboard('Order canceled.')
                );

                $orderRepository->save($this->order);

                $orderService->syncPlatformWith($this->order);
            }

            $message = "Charge canceled with success";
            return new ServiceResponse(true, $message);
        }

        return new ServiceResponse(false, $resultApi);
    }
}