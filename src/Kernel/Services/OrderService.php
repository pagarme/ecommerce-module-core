<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractDataService;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Interfaces\ResponseHandlerInterface;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;

use Mundipagg\Core\Payment\Aggregates\Order as PaymentOrder;

final class OrderService
{
    private $logService;

    public function __construct()
    {
        $this->logService = new OrderLogService();
    }

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
        $orderRepository = new OrderRepository();
        $savedOrder = $orderRepository->findByMundipaggId($order->getMundipaggId());
        if ($savedOrder !== null) {
            $order = $savedOrder;
        }

        if ($order->getStatus()->equals(OrderStatus::canceled())) {
            return;
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

        $i18n = new LocalizationService();

        if (empty($results)) {
            $order->getPlatformOrder()->addHistoryComment(
                $i18n->getDashboard(
                    "Order '%s' canceled at Mundipagg",
                    $order->getMundipaggId()->getValue()
                )
            );
            $order->setStatus(OrderStatus::canceled());
            $orderRepository->save($order);
            $order->getPlatformOrder()->save();
            return;
        }

        $history = $i18n->getDashboard("Some charges couldn't be canceled at Mundipagg. Reasons:");
        $history .= "<br /><ul>";
        foreach ($results as $chargeId => $reason)
        {
            $history .= "<li>$chargeId : $reason</li>";
        }
        $history .= '</ul>';
        $order->getPlatformOrder()->addHistoryComment($history);
        $order->getPlatformOrder()->save();
    }

    public function cancelAtMundipaggByPlatformOrder(PlatformOrderInterface $platformOrder)
    {
        $orderId = $platformOrder->getMundipaggId();
        if (empty($orderId)) {
            return;
        }

        $APIService = new APIService();

        $order = $APIService->getOrder($orderId);
        if (is_a($order, Order::class)) {
            $this->cancelAtMundipagg($order);
        }
    }

    public function createOrderAtMundipagg(PlatformOrderInterface $platformOrder)
    {
        $orderInfo = $this->getOrderInfo($platformOrder);

        $this->logService->orderInfo(
            $platformOrder->getCode(),
            'Creating order.',
            $orderInfo
        );
        //set pending
        $platformOrder->setState(OrderState::stateNew());
        $platformOrder->setStatus(OrderStatus::pending());
        $platformOrder->save();

        //build PaymentOrder based on platformOrder
        $order =  $this->extractPaymentOrderFromPlatformOrder($platformOrder);

        //Send through the APIService to mundipagg
        $apiService = new APIService();
        $response = $apiService->createOrder($order);

        $handler = $this->getResponseHandler($response);
        $handler->handle($response);

        return [$response];
    }

    /** @return ResponseHandlerInterface */
    private function getResponseHandler($response)
    {
        $responseClass = get_class($response);
        $responseClass = explode('\\', $responseClass);

        $responseClass =
            'Mundipagg\\Core\\Payment\\Services\\ResponseHandlers\\' .
            end($responseClass) . 'Handler';

        return new $responseClass;
    }

    /** @Todo do the validations */
    private function extractPaymentOrderFromPlatformOrder(
        PlatformOrderInterface $platformOrder
    )
    {
        $user = new Customer();
        $user->setType(CustomerType::individual());

        $order = new PaymentOrder();
        $order->setCustomer($platformOrder->getCustomer());

        $payments = $platformOrder->getPaymentMethodCollection();
        foreach ($payments as $payment) {
            $order->addPayment($payment);
        }

        $items = $platformOrder->getItemCollection();
        foreach ($items as $item) {
            $order->addItem($item);
        }

        $order->setCode($platformOrder->getCode());
        //@todo get antfraud config from module configuration
        $order->setAntifraudEnabled(false);

        $shipping = $platformOrder->getShipping();
        if ($shipping !== null) {
            $order->setShipping($shipping);
        }

        return $order;
    }

    /**
     * @param PlatformOrderInterface $platformOrder
     * @return \stdClass
     */
    private function getOrderInfo(PlatformOrderInterface $platformOrder)
    {
        $orderInfo = new \stdClass();
        $orderInfo->grandTotal = $platformOrder->getGrandTotal();
        return $orderInfo;
    }
}