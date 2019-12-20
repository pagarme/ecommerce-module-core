<?php

namespace Mundipagg\Core\Recurrence\Services\ResponseHandlers;

use \Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Recurrence\Services\ResponseHandlers\AbstractResponseHandler;
use Mundipagg\Core\Kernel\Abstractions\AbstractDataService;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\InvoiceService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\InvoiceState;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus as OrderStatus;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;
use Mundipagg\Core\Payment\Aggregates\Order as PaymentOrder;
use Mundipagg\Core\Payment\Factories\SavedCardFactory;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;
use Mundipagg\Core\Payment\Repositories\SavedCardRepository;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\Factories\SubscriptionFactory;
use Mundipagg\Core\Recurrence\Repositories\SubscriptionRepository;

final class SubscriptionHandler extends AbstractResponseHandler
{
    private $order;

    /**
     * @param Order $createdOrder
     * @return mixed
     */
    public function handle(Subscription $subscription)
    {
        /**
         * @fixMe
         * $status = ucfirst($createdOrder->getStatus()->getStatus());
        */
        $status = 'Paid';

        $statusHandler = 'handleSubscriptionStatus' . $status;

        $platformOrderStatus = $status;

        $this->logService->orderInfo(
            $subscription->getCode(),
            "Handling order status: 'Paid'"
        );

        $orderFactory = new OrderFactory();
        $this->order =
            $orderFactory->createFromSubscriptionData(
                $subscription,
                $platformOrderStatus
            );

        $subscriptionRepository = new SubscriptionRepository();
        //$subscriptionRepository->save($subscription);

        //$this->saveCustomer($subscription);

        return $this->$statusHandler($subscription);
    }

    private function handleSubscriptionStatusPaid(Subscription $subscription)
    {
        $invoiceService = new InvoiceService();

        $order = $this->order;

        $cantCreateReason = $invoiceService->getInvoiceCantBeCreatedReason($order);
        $invoice = $invoiceService->createInvoiceFor($order);
        if ($invoice !== null) {

            $this->completePayment($order, $subscription, $invoice);
            //$this->saveCards($order);

            return true;
        }
        return $cantCreateReason;
    }

    /*private function handleOrderStatusProcessing(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Order waiting for online retries at Mundipagg.' .
                ' MundipaggId: ' . $order->getMundipaggId()->getValue()
            )
        );

        return $this->handleOrderStatusPending($order);
    }*/

    /**
     * @param Order $order
     * @return bool
     */
    /*private function handleSubscriptionStatusActive(Order $order)
    {
        $this->createAuthorizationTransaction($order);

        $order->setStatus(OrderStatus::pending());
        $platformOrder = $order->getPlatformOrder();

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Order created at Mundipagg. Id: %s',
                $order->getMundipaggId()->getValue()
            )
        );

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);
        return true;
    }*/

    /**
     * @param Order $order
     * @param $invoice
     */
    private function completePayment(Order $order, Subscription $subscription, $invoice)
    {
        $invoice->setState(InvoiceState::paid());
        $invoice->save();
        $platformOrder = $order->getPlatformOrder();

        $this->createCaptureTransaction($order);

        $order->setStatus(OrderStatus::processing());
        //@todo maybe an Order Aggregate should have a State too.
        $platformOrder->setState(OrderState::processing());

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Subscription invoice paid.') . '<br>' .
            ' MundipaggId: ' . $subscription->getMundipaggId()->getValue() . '<br>' .
            $i18n->getDashboard('Invoice') . ': ' .
            $subscription->getInvoice()->getMundipaggId()->getValue()
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);
    }

    private function createCaptureTransaction(Order $order)
    {
        /**
         * @todo Decide if we have to create platform transactions
         */
    }

    private function createAuthorizationTransaction(Order $order)
    {
        /**
         * @todo Decide if we have to create platform transactions
         */
    }

    /*private function handleOrderStatusCanceled(Order $order)
    {
        return $this->handleOrderStatusFailed($order);
    }*/

    /*private function handleOrderStatusFailed(Order $order)
    {
        $charges = $order->getCharges();

        $acquirerMessages = '';
        $historyData = [];
        foreach ($charges as $charge) {
            $lastTransaction = $charge->getLastTransaction();
            $acquirerMessages .=
                "{$charge->getMundipaggId()->getValue()} => '{$lastTransaction->getAcquirerMessage()}', ";
            $historyData[$charge->getMundipaggId()->getValue()] = $lastTransaction->getAcquirerMessage();

        }
        $acquirerMessages = rtrim($acquirerMessages, ', ') ;

        $this->logService->orderInfo(
            $order->getCode(),
            "Order creation Failed: $acquirerMessages"
        );

        $i18n = new LocalizationService();
        $historyComment = $i18n->getDashboard('Order payment failed');
        $historyComment .= ' (' . $order->getMundipaggId()->getValue() . ') : ';

        foreach ($historyData as $chargeId => $acquirerMessage) {
            $historyComment .= "$chargeId => $acquirerMessage; ";
        }
        $historyComment = rtrim($historyComment, '; ');
        $order->getPlatformOrder()->addHistoryComment(
            $historyComment
        );

        $order->setStatus(OrderStatus::canceled());
        $order->getPlatformOrder()->setState(OrderState::canceled());
        $order->getPlatformOrder()->save();

        $order->getPlatformOrder()->addHistoryComment(
            $i18n->getDashboard('Order canceled.')
        );

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        return "One or more charges weren't authorized. Please try again.";
    }*/

    /**
     * @param PaymentOrder $paymentOrder
     */
    /*private function saveCustomer(Order $createdOrder)
    {
        $customer = $createdOrder->getCustomer();

        //save only registered customers;
        if(empty($customer) || $customer->getCode() === null) {
            return;
        }

        $customerRepository = new CustomerRepository();

        if ($customerRepository->findByCode($customer->getCode()) !== null) {
            $customerRepository->deleteByCode($customer->getCode());
        }

        if (
            $customerRepository->findByMundipaggId($customer->getMundipaggId()) === null
        ) {
            $customerRepository->save($customer);
        }
    }*/

    /*private function saveCards(Order $order)
    {
        $savedCardFactory = new SavedCardFactory();
        $savedCardRepository = new SavedCardRepository();
        $charges = $order->getCharges();

        foreach ($charges as $charge) {
            $lastTransaction = $charge->getLastTransaction();
            if ($lastTransaction === null) {
                continue;
            }
            if (
                !$lastTransaction->getTransactionType()->equals(
                    TransactionType::creditCard()
                )
            ) {
                continue; //save only credit card transactions;
            }

            $metadata = $charge->getMetadata();
            $saveOnSuccess =
                isset($metadata->saveOnSuccess) &&
                $metadata->saveOnSuccess === "true";

            if (
                !empty($lastTransaction->getCardData()) &&
                $saveOnSuccess &&
                $order->getCustomer()->getMundipaggId()->equals(
                    $charge->getCustomer()->getMundipaggId()
                )
            ) {
                $postData =
                    json_decode(json_encode($lastTransaction->getCardData()));
                $postData->owner =
                    $charge->getCustomer()->getMundipaggId();

                $savedCard = $savedCardFactory->createFromTransactionJson($postData);
                if (
                    $savedCardRepository->findByMundipaggId($savedCard->getMundipaggId()) === null
                ) {
                    $savedCardRepository->save($savedCard);
                    $this->logService->orderInfo(
                        $order->getCode(),
                        "Card '{$savedCard->getMundipaggId()->getValue()}' saved."
                    );

                }
            }
        }
    }*/
}