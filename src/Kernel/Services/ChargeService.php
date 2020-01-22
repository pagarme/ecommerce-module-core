<?php

namespace Mundipagg\Core\Kernel\Services;

use MundiAPILib\Models\GetChargeResponse;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Repositories\ChargeRepository;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Responses\ServiceResponse;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Payment\Services\ResponseHandlers\OrderHandler;
use Mundipagg\Core\Webhook\Services\ChargeHandlerService;
use Unirest\Exception;

class ChargeService
{
    /** @var LogService  */
    protected $logService;

    public function __construct()
    {
        $this->logService = new LogService(
            'ChargeService',
            true
        );
    }

    public function captureById($chargeId, $amount = 0)
    {
        try {

            $chargeRepository = new ChargeRepository();
            $charge = $chargeRepository->findByMundipaggId(
                new ChargeId($chargeId)
            );

            if ($charge === null) {
                throw new Exception("Charge not found");
            }

            return $this->capture($charge, $amount);

        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function cancelById($chargeId, $amount = 0)
    {
        try {

            $chargeRepository = new ChargeRepository();
            $charge = $chargeRepository->findByMundipaggId(
                new ChargeId($chargeId)
            );

            if ($charge === null) {
                throw new Exception("Charge not found");
            }

            return $this->cancel($charge, $amount);

        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function capture(Charge $charge, $amount = 0)
    {
        $order = (new OrderRepository)->findByMundipaggId(
            new OrderId($charge->getOrderId()->getValue())
        );

        $this->logService->info("Charge capture");
        $orderRepository = new OrderRepository();
        $orderService = new OrderService();
        $chargeHandlerService = new ChargeHandlerService();

        $platformOrder = $order->getPlatformOrder();

        $apiService = new APIService();
        $this->logService->info(
            "Capturing charge on Mundipagg - " . $charge->getMundipaggId()->getValue()
        );

        $resultApi = $apiService->captureCharge($charge, $amount);

        if ($resultApi instanceof GetChargeResponse) {

            if (!$charge->getStatus()->equals(ChargeStatus::paid())) {
                $this->logService->info(
                    "Pay charge - " . $charge->getMundipaggId()->getValue()
                );
                $charge->pay($amount);
            }

            if ($charge->getPaidAmount() == 0) {
                $charge->setPaidAmount($amount);
            }

            $this->logService->info("Update Charge on Order");
            $order->updateCharge($charge);
            $orderRepository->save($order);

            $this->logService->info("Adding history on Order");
            $history = $chargeHandlerService->prepareHistoryComment($charge);
            $platformOrder->addHistoryComment($history);

            $this->logService->info("Synchronizing with platform Order");
            $orderService->syncPlatformWith($order);

            $this->logService->info("Change Order status");
            $order->setStatus(OrderStatus::paid());
            $orderHandlerService = new OrderHandler();
            $orderHandlerService->handle($order);

            $message = $chargeHandlerService->prepareReturnMessage($charge);

            return new ServiceResponse(true, $message);
        }

        return new ServiceResponse(false, $resultApi);
    }

    public function cancel(Charge $charge, $amount = 0)
    {

        $order = (new OrderRepository)->findByMundipaggId(
            new OrderId($charge->getOrderId()->getValue())
        );

        $this->logService->info("Charge cancel");

        $orderRepository = new OrderRepository();
        $orderService = new OrderService();
        $moneyService = new MoneyService();
        $chargeHandlerService = new ChargeHandlerService();
        $i18n = new LocalizationService();

        $platformOrder = $order->getPlatformOrder();

        $apiService = new APIService();
        $this->logService->info(
            "Cancel charge on Mundipagg - " . $charge->getMundipaggId()->getValue()
        );

        $resultApi = $apiService->cancelCharge($charge, $amount);

        if ($resultApi === null) {

            $this->logService->info("Update Charge on Order");
            $order->updateCharge($charge);
            $orderRepository->save($order);
            $history = $chargeHandlerService->prepareHistoryComment($charge);

            $this->logService->info("Adding history on Order");
            $order->getPlatformOrder()->addHistoryComment($history);

            $this->logService->info("Synchronizing with platform Order");
            $orderService->syncPlatformWith($order);

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

                $order->setStatus(OrderStatus::canceled());
                $order->getPlatformOrder()->setState(OrderState::canceled());
                $order->getPlatformOrder()->save();

                $orderRepository->save($order);
                $orderService->syncPlatformWith($order);

                $statusOrderLabel = $platformOrder->getStatusLabel(
                    $order->getStatus()
                );

                $messageComplementEmail = $i18n->getDashboard(
                    'New order status: %s',
                    $statusOrderLabel
                );

                $sender = $platformOrder->sendEmail($messageComplementEmail);

                $order->getPlatformOrder()->addHistoryComment(
                    $i18n->getDashboard('Order canceled.'),
                    $sender
                );
            }

            $message = $i18n->getDashboard("Charge canceled with success");
            return new ServiceResponse(true, $message);
        }

        return new ServiceResponse(false, $resultApi);
    }
}