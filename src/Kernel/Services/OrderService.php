<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractDataService;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Interfaces\ResponseHandlerInterface;
use Mundipagg\Core\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Factories\ChargeFactory;
use Mundipagg\Core\Payment\Aggregates\Order as PaymentOrder;
use Exception;

final class OrderService
{
    private $logService;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    public function __construct()
    {
        $this->logService = new OrderLogService();
        $this->orderRepository = new OrderRepository();
    }

    /**
     *
     * @param Order $order
     * @param bool $changeStatus
     */
    public function syncPlatformWith(Order $order, $changeStatus = true)
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

        if ($changeStatus) {
            $this->changeOrderStatus($order);
        }

        $platformOrder->save();
    }

    public function changeOrderStatus(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();
        $orderStatus = $order->getStatus();
        if ($orderStatus->equals(OrderStatus::paid())) {
            $orderStatus = OrderStatus::processing();
        }

        //@todo In the future create a core status machine with the platform
        if (!$order->getPlatformOrder()->getState()->equals(OrderState::closed())) {
            $platformOrder->setStatus($orderStatus);
        }
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
            $order->setStatus(OrderStatus::canceled());
            $order->getPlatformOrder()->setStatus(OrderStatus::canceled());

            $orderRepository->save($order);
            $order->getPlatformOrder()->save();

            $statusOrderLabel = $order->getPlatformOrder()->getStatusLabel(
                $order->getStatus()
            );

            $messageComplementEmail = $i18n->getDashboard(
                'New order status: %s',
                $statusOrderLabel
            );

            $sender = $order->getPlatformOrder()->sendEmail($messageComplementEmail);

            $order->getPlatformOrder()->addHistoryComment(
                $i18n->getDashboard(
                    "Order '%s' canceled at Mundipagg",
                    $order->getMundipaggId()->getValue()
                ),
                $sender
            );

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

    /**
     * @param PlatformOrderInterface $platformOrder
     * @return array
     * @throws \Exception
     */
    public function createOrderAtMundipagg(PlatformOrderInterface $platformOrder)
    {
        try {
            $orderInfo = $this->getOrderInfo($platformOrder);

            $this->logService->orderInfo(
                $platformOrder->getCode(),
                'Creating order.',
                $orderInfo
            );
            //set pending
            $platformOrder->setState(OrderState::stateNew());
            $platformOrder->setStatus(OrderStatus::pending());

            //build PaymentOrder based on platformOrder
            $order =  $this->extractPaymentOrderFromPlatformOrder($platformOrder);

            $i18n = new LocalizationService();

            //Send through the APIService to mundipagg
            $apiService = new APIService();
            $response = $apiService->createOrder($order);

            $originalResponse = $response;
            $forceCreateOrder = MPSetup::getModuleConfiguration()->isCreateOrderEnabled();

            if (!$forceCreateOrder && !$this->checkResponseStatus($response)) {
                $this->persistListChargeFailed($response);

                $message = $i18n->getDashboard("Can't create order.");
                throw new \Exception($message, 400);
            }

            $platformOrder->save();

            $orderFactory = new OrderFactory();
            $response = $orderFactory->createFromPostData($response);

            $response->setPlatformOrder($platformOrder);

            $handler = $this->getResponseHandler($response);
            $handler->handle($response, $order);

            $platformOrder->save();

            if ($forceCreateOrder && !$this->checkResponseStatus($originalResponse)) {
                $message = $i18n->getDashboard("Can't create order.");
                throw new \Exception($message, 400);
            }

            return [$response];
        } catch (\Exception $e) {
            $exceptionHandler = new ErrorExceptionHandler();
            $paymentOrder = new PaymentOrder();
            $paymentOrder->setCode($platformOrder->getcode());
            $frontMessage = $exceptionHandler->handle($e, $paymentOrder);

            throw new \Exception($frontMessage, 400);
        }
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

    public function extractPaymentOrderFromPlatformOrder(
        PlatformOrderInterface $platformOrder
    ) {
        $moduleConfig = MPSetup::getModuleConfiguration();

        $moneyService = new MoneyService();

        $user = new Customer();
        $user->setType(CustomerType::individual());

        $order = new PaymentOrder();

        $order->setAmount(
            $moneyService->floatToCents(
                $platformOrder->getGrandTotal()
            )
        );
        $order->setCustomer($platformOrder->getCustomer());
        $order->setAntifraudEnabled($moduleConfig->isAntifraudEnabled());
        $order->setPaymentMethod($platformOrder->getPaymentMethod());

        $payments = $platformOrder->getPaymentMethodCollection();
        foreach ($payments as $payment) {
            $order->addPayment($payment);
        }

        if (!$order->isPaymentSumCorrect()) {
            throw new \Exception(
                'The sum of payments is different than the order amount!',
                400
            );
        }

        $items = $platformOrder->getItemCollection();
        foreach ($items as $item) {
            $order->addItem($item);
        }

        $order->setCode($platformOrder->getCode());

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
    public function getOrderInfo(PlatformOrderInterface $platformOrder)
    {
        $orderInfo = new \stdClass();
        $orderInfo->grandTotal = $platformOrder->getGrandTotal();
        return $orderInfo;
    }

    /**
     * @param $response
     * @return boolean
     */
    private function checkResponseStatus($response)
    {
        if (
            !isset($response['status']) ||
            !isset($response['charges']) ||
            $response['status'] == 'failed'
        ) {
            return false;
        }

        foreach ($response['charges'] as $charge) {
            if (isset($charge['status']) && $charge['status'] == 'failed') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $response
     * @throws InvalidParamException
     * @throws Exception
     */
    private function persistListChargeFailed($response)
    {
        if (empty($response['charges'])) {
            return;
        }

        $chargeFactory = new ChargeFactory();
        $chargeService = new ChargeService();

        foreach ($response['charges'] as $chargeResponse) {
            $order = ['order' => ['id' => $response['id']]];
            $charge = $chargeFactory->createFromPostData(
                array_merge($chargeResponse, $order)
            );

            $chargeService->save($charge);
        }
    }

    /**
     * @return Order|null
     * @throws InvalidParamException
     */
    public function getOrderByMundiPaggId(OrderId $orderId)
    {
        return $this->orderRepository->findByMundipaggId($orderId);
    }

    /**
     * @param string $platformOrderID
     * @return Order|null
     */
    public function getOrderByPlatformId($platformOrderID)
    {
        return $this->orderRepository->findByPlatformId($platformOrderID);
    }
}
