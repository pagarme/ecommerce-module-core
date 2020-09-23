<?php

namespace Mundipagg\Core\Recurrence\Factories;

use MundiAPILib\Models\ListInvoicesResponse;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Kernel\ValueObjects\Id\InvoiceId;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod as PaymentMethod;
use Mundipagg\Core\Recurrence\Aggregates\SubscriptionItem;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionItemId;

class InvoiceFactory implements FactoryInterface
{
    /** @var Invoice  */
    public $invoice;

    public function __construct()
    {
        $this->invoice = new Invoice();
    }
    public function createFromPostData($postData)
    {
        $postData = json_decode(json_encode($postData));
        $this->invoice->setMundipaggId(new InvoiceId($postData->id));
        $this->setSubscriptionId($postData);
        $this->setItems($postData);
        $this->setCycle($postData);

        return $this->invoice;
    }

    protected function setCycle($postData)
    {
        if (empty($postData->cycle)) {
            return;
        }

        $cycleData = (array) $postData->cycle;
        $cycleFactory = new CycleFactory();
        $cycle = $cycleFactory->createFromPostData($cycleData);
        $this->invoice->setCycle($cycle);
    }

    protected function setItems($postData)
    {
        if (!empty($postData->items)) {
            foreach ($postData->items as $item) {
                $this->setItem($item);
            }
        }
    }

    protected function setItem($item)
    {
        if (empty($item->name)) {
            return;
        }

        $subscriptionItem = new SubscriptionItem();
        $subscriptionItem->setMundipaggId(
            new SubscriptionItemId($item->subscription_item_id)
        );
        $subscriptionItem->setQuantity($item->quantity);

        $this->invoice->addItem($subscriptionItem);

    }

    protected function setSubscriptionId($postData)
    {
        if (!empty($postData->subscriptionId)) {
            $subscriptionId = new SubscriptionId($postData->subscriptionId);
            $this->invoice->setSubscriptionId($subscriptionId);
            return;
        }

        if (!empty($postData->subscription->id)) {
            $subscriptionId = new SubscriptionId($postData->subscription->id);
            $this->invoice->setSubscriptionId($subscriptionId);
            return;
        }

    }

    public function createFromCharge(Charge $charge)
    {
        $this->invoice->setMundipaggId(new InvoiceId($charge->getInvoiceId()));
        $this->invoice->setSubscriptionId(new SubscriptionId($charge->getSubscriptionId()));
        $this->invoice->setPaymentMethod($charge->getPaymentMethod()->getPaymentMethod());
        $this->invoice->setAmount($charge->getAmount());
        $this->invoice->setStatus($charge->getStatus());

        return $this->invoice;
    }

    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }

    /**
     * @param $response
     * @return Invoice
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function createFromApiResponseData($response)
    {
        $postData = json_decode(json_encode($response));
        if (empty($postData->data[0])) {
            throw new \Exception("Can't get invoice data", 400);
        }
        $data = $postData->data[0];

        $this->invoice->setMundipaggId(new InvoiceId($data->id));
        $this->invoice->setId($data->id); /** Just filling missing field  **/
        $this->invoice->setSubscriptionId(new SubscriptionId($data->subscription->id));
        $this->invoice->setAmount($data->amount);
        $this->invoice->setStatus($data->status);
        $this->invoice->setpaymentMethod($data->payment_method);
        $this->invoice->setInstallments($data->installments);
        $this->invoice->setTotalDiscount($data->total_discount);
        $this->invoice->setTotalIncrement($data->total_increment);
        $this->setCustomer($data, $this->invoice);
        $this->setCharge($data, $this->invoice);

        if (isset($data->cycle)) {
            $cycleFactory = new CycleFactory();
            $cycle = $cycleFactory->createFromPostData((array) $data->cycle);
            $this->invoice->setCycle($cycle);
        }
        return $this->invoice;
    }

    private function setCustomer($data, &$invoice)
    {
        $customer = new Customer();
        $customerId = new CustomerId($data->customer->id);
        $customer->setMundipaggId($customerId);
        $this->invoice->setCustomer($customer);
    }

    private function setCharge($data, &$invoice)
    {
        $charge = new Charge();
        $chargeId = new ChargeId($data->charge->id);
        $charge->setMundipaggId($chargeId);
        $charge->setAmount($data->charge->amount);
        $this->invoice->setCharge($charge);
    }
}
