<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as ‌‌MPSetup;
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
use Mundipagg\Core\Payment\Aggregates\Shipping;
use Mundipagg\Core\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\Factories\SubProductFactory;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\PricingSchemeValueObject as PricingScheme;
use MundiPagg\MundiPagg\Model\Source\Interval;

final class SubscriptionService
{
    private $logService;
    private $subscriptionItems;

    public function __construct()
    {
        $this->logService = new OrderLogService();
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
            $paymentMethod = $platformOrder->getPaymentMethod();
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

        if (empty($items[0]) || count($items) == 0) {
            throw new \Exception('Recurrence items not found', 400);
        }

        $recurrenceSettings = $items[0];

        $this->fillCreditCardData($subscription, $order);
        $this->fillSubscriptionItems($subscription, $order, $recurrenceSettings);
        $this->fillInterval($subscription);
        $this->fillBoletoData($subscription);
        $this->fillDescription($subscription);
        $this->fillShipping($subscription, $order);

        $subscription->setCode($order->getCode());
        $subscription->setCustomer($order->getCustomer());
        $subscription->setBillingType($recurrenceSettings->getBillingType());
        $subscription->setPaymentMethod($order->getPaymentMethod());

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

    private function fillCreditCardData(&$subscription, $order)
    {
        if ($this->paymentExists($order)) {
            $payments = $order->getPayments();

            $subscription->setCardToken(
                $this->extractCreditCardTokenFromPayment($payments[0])
            );
            $subscription->setInstallments(
                $this->extractInstallmentsFromPayment($payments[0])
            );
        }
    }

    private function fillBoletoData(&$subscription)
    {
        $boletoDays = ‌‌MPSetup::getModuleConfiguration()->getBoletoDueDays();
        $subscription->setBoletoDays($boletoDays);
    }

    private function fillSubscriptionItems(&$subscription, $order, $recurrenceSettings)
    {
        $this->subscriptionItems = $this->extractSubscriptionItemsFromOrder(
            $order,
            $recurrenceSettings
        );
        $subscription->setItems($this->subscriptionItems);
    }

    private function fillInterval(&$subscription)
    {
        /**
         * @todo Subscription Intervals are comming from subscription items
         */
        $intervalCount =
            $this->subscriptionItems[0]
                ->getSelectedRepetition()
                ->getIntervalCount();

        $intervalType =
            $this->subscriptionItems[0]
                ->getSelectedRepetition()
                ->getInterval();

        $subscription->setIntervalType($intervalType);
        $subscription->setIntervalCount($intervalCount);
    }

    private function fillDescription(&$subscription)
    {
        $subscription->setDescription($this->subscriptionItems[0]->getDescription());
    }

    private function fillShipping(&$subscription, $order)
    {
        $orderShipping = $order->getShipping();
        $subscription->setShipping($orderShipping);
    }

    private function paymentExists($order)
    {
        $payments = $order->getPayments();
        if (isset($payments) && isset($payments[0])) {
            return true;
        }

        return false;
    }

    private function extractCreditCardTokenFromPayment($payment)
    {
        if (method_exists($payment, 'getIdentifier')) {
            return $payment->getIdentifier()->getValue();
        }

        return null;
    }

    private function extractInstallmentsFromPayment($payment)
    {
        if (method_exists($payment, 'getInstallments')) {
            return $payment->getInstallments();
        }
    }
}