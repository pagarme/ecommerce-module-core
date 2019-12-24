<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Factories\ChargeFactory;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Kernel\Services\OrderLogService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Order as PaymentOrder;
use Mundipagg\Core\Payment\Aggregates\Shipping;
use Mundipagg\Core\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\Factories\InvoiceFactory;
use Mundipagg\Core\Recurrence\Factories\SubProductFactory;
use Mundipagg\Core\Recurrence\Factories\SubscriptionFactory;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\PricingSchemeValueObject as PricingScheme;
use MundiPagg\MundiPagg\Model\Source\Interval;

final class SubscriptionService
{
    private $logService;
    private $subscriptionItems;
    private $apiService;

    public function __construct()
    {
        $this->logService = new OrderLogService();
        $this->apiService = new APIService();
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
            $this->setPlatformOrderPending($platformOrder);

            //build PaymentOrder based on platformOrder
            $order = $orderService->extractPaymentOrderFromPlatformOrder($platformOrder);
            $subscription = $this->extractSubscriptionDataFromOrder($order);

            //Send through the APIService to mundipagg
            $subscriptionResponse = $this->apiService->createSubscription($subscription);
            $this->getSubscriptionMissingData($subscriptionResponse);

            if (!$this->checkResponseStatus($subscriptionResponse)) {
                $i18n = new LocalizationService();
                $message = $i18n->getDashboard("Can't create order.");

                throw new \Exception($message, 400);
            }

            $platformOrder->save();

            $subscriptionFactory = new SubscriptionFactory();
            $response = $subscriptionFactory->createFromPostData($subscriptionResponse);

            $response->setPlatformOrder($platformOrder);

            $handler = $this->getResponseHandler($response);
            $handler->handle($response);
            $platformOrder->save();

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

    /**
     * @param PaymentOrder $order
     * @return array
     */
    private function getSubscriptionItems(PaymentOrder $order)
    {
        $recurrenceService = new RecurrenceService();
        $items = [];

        foreach ($order->getItems() as $product) {
            if ($product->getSelectedOption()) {
                $items[] =
                    $recurrenceService
                        ->getRecurrenceProductByProductId(
                            $product->getCode()
                        );
            }
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
            $pricingScheme = PricingScheme::UNIT($item->getAmount());

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
        $boletoDays = MPSetup::getModuleConfiguration()->getBoletoDueDays();
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
        if (empty($this->subscriptionItems[0]->getSelectedRepetition())) {
            return;
        }

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
        /** @todo
         * Multiply shipping for cycles
         **/
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

    private function checkResponseStatus($response)
    {
        if (
            !isset($response['status']) ||
            $response['status'] == 'failed'
        ) {
            return false;
        }

        return true;
    }

    public function isSubscription($platformOrder)
    {
        $orderService = new OrderService();
        $order = $orderService->extractPaymentOrderFromPlatformOrder($platformOrder);
        $subscriptionItem = $this->getSubscriptionItems($order);

        if (count($subscriptionItem) == 0) {
            return false;
        }

        return true;
    }

    /**
     * @param Subscription $response
     * @return string
     */
    private function getResponseHandler($response)
    {
        $responseClass = get_class($response);
        $responseClass = explode('\\', $responseClass);

        $responseClass =
            'Mundipagg\\Core\\Recurrence\\Services\\ResponseHandlers\\' .
            end($responseClass) . 'Handler';

        return new $responseClass;
    }

    private function setPlatformOrderPending(&$platformOrder)
    {
        //First platform order status and state after subscription creation success
        $platformOrder->setState(OrderState::stateNew());
        $platformOrder->setStatus(OrderStatus::pending());
    }

    private function getSubscriptionMissingData(&$subscriptionResponse)
    {
        $subscriptionResponse['invoice'] =
            $this->getInvoiceFromSubscriptionResponse(
                $subscriptionResponse
            );
        $subscriptionResponse['charge'] = $this->getChargeFromInvoiceResponse(
            $subscriptionResponse['invoice']
        );
    }

    /**
     * @param $subscriptionResponse
     * @return Invoice
     */
    private function getInvoiceFromSubscriptionResponse($subscriptionResponse)
    {
        $subscriptionId = new SubscriptionId($subscriptionResponse['id']);

        $invoiceResponse = $this->apiService->getSubscriptionInvoice($subscriptionId);
        $invoiceFactory = new InvoiceFactory();

        return $invoiceFactory->createFromApiResponseData($invoiceResponse);
    }

    /**
     * @param $invoiceResponse
     * @return Charge
     */
    private function getChargeFromInvoiceResponse($invoiceResponse)
    {
        $chargeResponse = $this->apiService->getCharge(
            $invoiceResponse->getCharge()->getMundipaggId()
        );

        $chargeFactory = new ChargeFactory();

        return $chargeFactory->createFromPostData($chargeResponse);
    }
}