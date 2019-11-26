<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Factories\TransactionFactory;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Payment\Factories\CustomerFactory;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod;

class ChargeFactory extends TreatFactoryChargeDataBase implements FactoryInterface
{
    /**
     * @var Charge
     */
    private $charge;

    /**
     * ChargeFactory constructor.
     */
    public function __construct()
    {
        $this->charge = new Charge();
    }

    private function setId($id)
    {
        $this->charge->setId($id);
    }

    private function setMundiPaggId($id)
    {
        $this->charge->setMundipaggId(new ChargeId($id));
    }

    private function setCode($code)
    {
        $this->charge->setCode($code);
    }

    private function setAmount($amount)
    {
        $this->charge->setAmount($amount);
    }

    private function setPaidAmount($paidAmount)
    {
        $paidAmount = isset($postData['paid_amount']) ? $postData['paid_amount'] : 0;
        $this->charge->setPaidAmount($paidAmount);
    }

    private function setPaymentMethod($paymentMethod)
    {
        $this->charge->setPaymentMethod(PaymentMethod::{$paymentMethod}());
    }

    private function setStatus($status)
    {
        $this->charge->setStatus(ChargeStatus::{$status}());
    }

    private function setCanceledAmount($canceledAmount)
    {
        $this->charge->setCanceledAmount($canceledAmount);
    }

    private function setRefundedAmount($refundedAmount){
        $this->charge->setRefundedAmount($refundedAmount);
    }

    private function setMetadata($data)
    {
        if (!empty($data['metadata'])) {
            $metadata = json_decode(json_encode($data['metadata']));
            $this->charge->setMetadata($metadata);
        }
    }

    private function setCustomer($data)
    {
        if (!empty($data['customer'])) {
            $customerFactory = new CustomerFactory();
            $customer = $customerFactory->createFromPostData($data['customer']);
            $this->charge->setCustomer($customer);
        }
    }

    private function setInvoice($data)
    {
        if (!empty($data['invoice'])) {
            $invoiceFactory = new InvoiceFactory();
            $invoice = $invoiceFactory->createFromPostData($data['invoice']);
            $this->charge->setInvoice($invoice);
        }
    }

    /**
     * @param $postData
     * @return mixed
     * @throws InvalidParamException
     */
    private function addTransaction($postData)
    {
        $lastTransactionData = null;
        if (isset($postData['last_transaction'])) {
            $lastTransactionData = $postData['last_transaction'];
        }

        if ($lastTransactionData !== null) {
            $transactionFactory = new TransactionFactory();
            $lastTransaction = $transactionFactory->createFromPostData($lastTransactionData);
            $lastTransaction->setChargeId($this->charge->getMundipaggId());

            $this->charge->addTransaction($lastTransaction);
        }
    }

    public function createFromPostData($postData)
    {
        $this->setMundiPaggId($postData['id']);
        $this->setCode($postData['code']);
        $this->setAmount($postData['amount']);
        $this->setPaidAmount($postData);
        $this->setPaymentMethod($postData['payment_method']);
        $this->addTransaction($postData);
        $this->setStatus($postData['status']);
        $this->setCustomer($postData);
        $this->setInvoice($postData);

        return $this->charge;
    }

    public function createFromDbData($dbData)
    {
        $this->setId($dbData['id']);
        $this->setMundiPaggId($dbData['mundipagg_id']);
        $this->setCode($dbData['code']);
        $this->setAmount($dbData['amount']);
        $this->setPaidAmount(intval($dbData['paid_amount']));
        $this->setCanceledAmount($dbData['canceled_amount']);
        $this->setRefundedAmount($dbData['refunded_amount']);
        $this->setStatus($dbData['status']);
        $this->setMetadata($dbData);
        $this->addTransaction($dbData);
        $this->setCustomer($dbData);
        $this->setInvoice($dbData);

        return $this->charge;
    }
}
