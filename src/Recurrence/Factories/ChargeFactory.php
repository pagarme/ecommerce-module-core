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
    public function createFromPostData($postData)
    {
        $charge = new Charge();

        $charge->setMundipaggId(new ChargeId($postData['id']));
        $charge->setCode($postData['code']);
        $charge->setAmount($postData['amount']);

        $paidAmount = isset($postData['paid_amount']) ? $postData['paid_amount'] : 0;
        $charge->setPaidAmount($paidAmount);
        $charge->setPaymentMethod(PaymentMethod::{$postData['payment_method']}());
        $this->addTransaction($postData, $charge);
        $charge->setStatus(ChargeStatus::$postData['status']());

        if (!empty($postData['metadata'])) {
            $metadata = json_decode(json_encode($postData['metadata']));
            $charge->setMetadata($metadata);
        }

        if (!empty($postData['customer'])) {
            $customerFactory = new CustomerFactory();
            $customer = $customerFactory->createFromPostData($postData['customer']);
            $charge->setCustomer($customer);
        }

        if (!empty($postData['invoice'])) {
            $invoiceFactory = new InvoiceFactory();
            $invoice = $invoiceFactory->createFromPostData($postData['invoice']);
            $charge->setInvoice($invoice);
        }

        return $charge;
    }

    public function createFromDbData($dbData)
    {
        $charge = new Charge();

        $charge->setId($dbData['id']);
        $charge->setMundipaggId(new ChargeId($dbData['mundipagg_id']));
        $charge->setCode($dbData['code']);
        $charge->setAmount($dbData['amount']);
        $charge->setPaidAmount(intval($dbData['paid_amount']));
        $charge->setCanceledAmount($dbData['canceled_amount']);
        $charge->setRefundedAmount($dbData['refunded_amount']);
        $charge->setStatus(ChargeStatus::$dbData['status']());

        if (!empty($dbData['metadata'])) {
            $metadata = json_decode($dbData['metadata']);
            $charge->setMetadata($metadata);
        }

        $transactionFactory = new TransactionFactory();
        $transactions = $this->extractTransactionsFromDbData($dbData);
        foreach ($transactions as $transaction) {
            $newTransaction = $transactionFactory->createFromDbData($transaction);
            $charge->addTransaction($newTransaction);
        }

        $customer = null;
        if (!empty($dbData['customer_id'])) {
            $customerRepository = new CustomerRepository();
            $customer = $customerRepository->findByMundipaggId(
                new CustomerId($dbData['customer_id'])
            );
        }

        if ($customer) {
            $charge->setCustomer($customer);
        }

        if (!empty($dbData['invoice'])) {
            $invoiceFactory = new InvoiceFactory();
            $invoice = $invoiceFactory->createFromPostData($dbData['invoice']);
            $charge->setInvoice($invoice);
        }

        return $charge;
    }

    /**
     * @param $postData
     * @param Charge $charge
     * @return mixed
     * @throws InvalidParamException
     */
    public function addTransaction($postData, Charge $charge)
    {
        $lastTransactionData = null;
        if (isset($postData['last_transaction'])) {
            $lastTransactionData = $postData['last_transaction'];
        }

        if ($lastTransactionData !== null) {
            $transactionFactory = new TransactionFactory();
            $lastTransaction = $transactionFactory->createFromPostData($lastTransactionData);
            $lastTransaction->setChargeId($charge->getMundipaggId());

            $charge->addTransaction($lastTransaction);
        }
    }
}
