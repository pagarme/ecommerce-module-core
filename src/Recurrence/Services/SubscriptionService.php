<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Kernel\Services\OrderLogService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Order as PaymentOrder;
use Mundipagg\Core\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\Factories\SubProductFactory;
use Mundipagg\Core\Recurrence\Repositories\SubscriptionRepository;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\PricingSchemeValueObject as PricingScheme;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus;
use MundiPagg\MundiPagg\Model\Source\Interval;

final class SubscriptionService
{
    private $logService;
    /**
     * @var LocalizationService
     */
    private $i18n;

    public function __construct()
    {
        $this->logService = new OrderLogService();
        $this->i18n = new LocalizationService();
    }

    public function createSubscriptionAtMundipagg(PlatformOrderInterface $platformOrder)
    {
        try {
            $orderService = new OrderService();
            $orderInfo = $orderService->getOrderInfo($platformOrder);

            $this->logService->orderInfo(
                $platformOrder->getCode(),
                'Creating order.',
                $orderInfo
            );
            //set pending
            $platformOrder->setState(OrderState::stateNew());
            $platformOrder->setStatus(OrderStatus::pending());

            //build PaymentOrder based on platformOrder
            $order = $orderService->extractPaymentOrderFromPlatformOrder($platformOrder);
            $subscription = $this->extractSubscriptionDataFromOrder($order);

            //Send through the APIService to mundipagg
            $apiService = new APIService();
            $response = $apiService->createSubscription($subscription);

            /*if ($this->checkResponseStatus($response)) {
                $i18n = new LocalizationService();
                $message = $i18n->getDashboard("Can't create order.");

                throw new \Exception($message, 400);
            }

            $platformOrder->save();

            $orderFactory = new OrderFactory();
            $response = $orderFactory->createFromPostData($response);

            $response->setPlatformOrder($platformOrder);

            $handler = $this->getResponseHandler($response);
            $handler->handle($response, $order);

            $platformOrder->save();*/

            return [$response];
        } catch(\Exception $e) {
            $exceptionHandler = new ErrorExceptionHandler;
            $paymentOrder = new PaymentOrder;
            $paymentOrder->setCode($platformOrder->getcode());
            $frontMessage = $exceptionHandler->handle($e, $paymentOrder);
            throw new \Exception($frontMessage, 400);
        }
    }

    private function extractSubscriptionDataFromOrder(PaymentOrder $order)
    {
        $subscription = new Subscription();

        $items = $this->getSubscriptionItems($order);

        if (count($items) == 0 || !isset($items[0])) {
            return;
        }

        $recurrenceSettings = $items[0];
        $payments = $order->getPayments();
        $cardToken = $order->getPayments()[0]->getIdentifier()->getValue();

        $subscriptionItems = $this->extractSubscriptionItemsFromOrder(
            $order,
            $recurrenceSettings
        );

        $intervalCount =
            $subscriptionItems[0]
                ->getSelectedRepetition()
                ->getIntervalCount();

        $intervalType =
            $subscriptionItems[0]
                ->getSelectedRepetition()
                ->getInterval();

        $subscription->setCode($order->getCode());
        $subscription->setPaymentMethod($order->getPaymentMethod());
        $subscription->setIntervalType($intervalType);
        $subscription->setIntervalCount($intervalCount);
        $subscription->setItems($subscriptionItems);
        $subscription->setCardToken($cardToken);
        $subscription->setBillingType($recurrenceSettings->getBillingType());
        $subscription->setCustomer($order->getCustomer());

        if ($payments[0]->getInstallments()) {
            $subscription->setInstallments($payments[0]->getInstallments());
        }
        $boletoDays = MPSetup::getModuleConfiguration()->getBoletoDueDays();
        $subscription->setBoletoDays($boletoDays);

        return $subscription;
    }

    private function getSubscriptionItems(PaymentOrder $order)
    {
        $recurrenceService = new RecurrenceService();
        $items = [];

        foreach ($order->getItems() as $product) {
            $items[] =
                $recurrenceService
                    ->getRecurrenceProductByProductId(
                        $product->getCode()
                    );
        }

        return $items;
    }

    private function extractSubscriptionItemsFromOrder($order, $recurrenceSettings)
    {
        $subscriptionItems = [];

        foreach ($order->getItems() as $item) {
            $subProduct = new SubProduct();

            $subProduct->setCycles($recurrenceSettings->getCycles());
            $subProduct->setDescription($item->getDescription());
            $subProduct->setQuantity($item->getQuantity());

            $itemPrice = PricingScheme::UNIT($item->getAmount());

            if ($item->getSelectedOption()->getRecurrencePrice()) {
                $itemPrice = $item->getSelectedOption()->getRecurrencePrice();
            }
            $pricingScheme = PricingScheme::UNIT($itemPrice);

            $subProduct->setPricingScheme($pricingScheme);
            $subProduct->setSelectedRepetition($item->getSelectedOption());

            $subscriptionItems[] = $subProduct;
        }

        return $subscriptionItems;
    }

    public function listAll()
    {
        return $this->getSubscriptionRepository()
            ->listEntities(0, false);
    }

    public function cancel($subscriptionId)
    {
        try {

            $subscription = $this->getSubscriptionRepository()
                ->find($subscriptionId);

            if (!$subscription) {
                $message = $this->i18n->getDashboard(
                    'Subscription not found'
                );

                $this->logService->orderInfo(
                    null,
                    $message . " ID {$subscriptionId} ."
                );

                return [
                    "message" => $message,
                    "code" => 404
                ];
            }

            if ($subscription->getStatus() == SubscriptionStatus::canceled()) {
                $message = $this->i18n->getDashboard(
                    'Subscription already canceled'
                );

                return [
                    "message" => $message,
                    "code" => 200
                ];
            }

            $apiService = new APIService();
            $apiService->cancelSubscription($subscription);

            $subscription->setStatus(SubscriptionStatus::canceled());
            $this->getSubscriptionRepository()->save($subscription);

            $message = $this->i18n->getDashboard(
                'Subscription canceled with success!'
            );

            return [
                "message" => $message,
                "code" => 200
            ];
        } catch (\Exception $exception) {

            $message = $this->i18n->getDashboard(
                'Error on cancel subscription'
            );

            $this->logService->orderInfo(
                null,
                $message . ' - ' . $exception->getMessage()
            );

            return [
                "message" => $message,
                "code" => 200
            ];
        }
    }

    public function getSubscriptionRepository()
    {
        return new SubscriptionRepository();
    }
}