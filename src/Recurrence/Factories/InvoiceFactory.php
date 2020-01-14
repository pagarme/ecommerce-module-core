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

class InvoiceFactory implements FactoryInterface
{
    public function createFromPostData($postData)
    {
        $postData = json_decode(json_encode($postData));
        $invoice = new Invoice();

        $invoice->setMundipaggId(new InvoiceId($postData->id));
        $invoice->setSubscriptionId(new SubscriptionId($postData->subscriptionId));

        return $invoice;
    }

    public function createFromCharge(Charge $charge)
    {
        $invoice = new Invoice();

        $invoice->setMundipaggId(new InvoiceId($charge->getInvoiceId()));
        $invoice->setSubscriptionId(new SubscriptionId($charge->getSubscriptionId()));
        $invoice->setPaymentMethod($charge->getPaymentMethod()->getPaymentMethod());
        $invoice->setAmount($charge->getAmount());
        $invoice->setStatus($charge->getStatus());

        return $invoice;
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
        $invoice = new Invoice();

        $invoice->setMundipaggId(new InvoiceId($data->id));
        $invoice->setId($data->id); /** Just filling missing field  **/
        $invoice->setSubscriptionId(new SubscriptionId($data->subscription->id));
        $invoice->setAmount($data->amount);
        $invoice->setStatus($data->status);
        $invoice->setpaymentMethod($data->payment_method);
        $invoice->setInstallments($data->installments);
        $invoice->setTotalDiscount($data->total_discount);
        $invoice->setTotalIncrement($data->total_increment);
        $this->setCustomer($data, $invoice);
        $this->setCharge($data, $invoice);

        if (isset($data->cycle)) {
            $cycleFactory = new CycleFactory();
            $cycle = $cycleFactory->createFromPostData((array) $data->cycle);
            $invoice->setCycle($cycle);
        }
        return $invoice;
    }

    private function setCustomer($data, &$invoice)
    {
        $customer = new Customer();
        $customerId = new CustomerId($data->customer->id);
        $customer->setMundipaggId($customerId);
        $invoice->setCustomer($customer);
    }

    private function setCharge($data, &$invoice)
    {
        $charge = new Charge();
        $chargeId = new ChargeId($data->charge->id);
        $charge->setMundipaggId($chargeId);
        $charge->setAmount($data->charge->amount);
        $invoice->setCharge($charge);
    }
}
