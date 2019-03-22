<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
//use Mundipagg\Core\Kernel\Factories\TransactionFactory;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Payment\Factories\CustomerFactory;
use Throwable;
use Zend\Console\Prompt\Char;

class ChargeFactory implements FactoryInterface
{
    /**
     *
     * @param  array $postData
     * @return Charge
     */
    public function createFromPostData($postData)
    {
        $charge = new Charge;
        $status = $postData['status'];

        $charge->setMundipaggId(new ChargeId($postData['id']));
        $charge->setCode($postData['code']);
        $charge->setAmount($postData['amount']);
        $paidAmount = isset($postData['paid_amount']) ? $postData['paid_amount'] : 0;
        $charge->setPaidAmount($paidAmount);
        $charge->setOrderId(new OrderId($postData['order']['id']));

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

        try {
            ChargeStatus::$status();
        }catch(Throwable $e) {
            throw new InvalidParamException(
                "Invalid charge status!",
                $status
            );
        }
        $charge->setStatus(ChargeStatus::$status());

        if (!empty($postData['metadata'])) {
            $metadata = json_decode(json_encode($postData['metadata']));
            $charge->setMetadata($metadata);
        }

        if (!empty($postData['customer'])) {
            $customerFactory = new CustomerFactory();
            $customer = $customerFactory->createFromPostData($postData['customer']);
            $charge->setCustomer($customer);
        }

        return $charge;
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        $charge = new Charge();

        $charge->setId($dbData['id']);
        $charge->setMundipaggId(new ChargeId($dbData['mundipagg_id']));
        $charge->setOrderId(new OrderId($dbData['order_id']));

        $charge->setCode($dbData['code']);

        $charge->setAmount($dbData['amount']);
        $charge->setPaidAmount($dbData['paid_amount']);
        $charge->setCanceledAmount($dbData['canceled_amount']);
        $charge->setRefundedAmount($dbData['refunded_amount']);

        $status = $dbData['status'];
        $charge->setStatus(ChargeStatus::$status());

        $transactionFactory = new TransactionFactory();
        $transactions = $this->extractTransactionsFromDbData($dbData);
        foreach ($transactions as $transaction) {
            $newTransaction = $transactionFactory->createFromDbData($transaction);
            $charge->addTransaction($newTransaction);
        }

        return $charge;
    }

    private function extractTransactionsFromDbData($dbData)
    {
        $transactions = [];
        if ($dbData['tran_id'] !== null) {
            $tranId = explode(',', $dbData['tran_id']);
            $tranMundipaggId = explode(',', $dbData['tran_mundipagg_id']);
            $tranChargeId = explode(',', $dbData['tran_charge_id']);
            $tranAmount = explode(',', $dbData['tran_amount']);
            $tranPaidAmount = explode(',', $dbData['tran_paid_amount']);
            $tranType = explode(',', $dbData['tran_type']);
            $tranStatus = explode(',', $dbData['tran_status']);
            $tranCreatedAt = explode(',', $dbData['tran_created_at']);

            $tranAcquirerNsu = explode(',', $dbData['tran_acquirer_nsu']);
            $tranAcquirerTid = explode(',', $dbData['tran_acquirer_tid']);
            $tranAcquirerAuthCode = explode(
                ',',
                $dbData['tran_acquirer_auth_code']
            );
            $tranAcquirerName = explode(',', $dbData['tran_acquirer_name']);
            $tranAcquirerMessage = explode(',', $dbData['tran_acquirer_message']);

            foreach ($tranId as $index => $id) {
                $transaction = [
                    'id' => $id,
                    'mundipagg_id' => $tranMundipaggId[$index],
                    'charge_id' => $tranChargeId[$index],
                    'amount' => $tranAmount[$index],
                    'paid_amount' => $tranPaidAmount[$index],
                    'type' => $tranType[$index],
                    'status' => $tranStatus[$index],
                    'acquirer_name' => $tranAcquirerName[$index],
                    'acquirer_tid' => $tranAcquirerTid[$index],
                    'acquirer_nsu' => $tranAcquirerNsu[$index],
                    'acquirer_auth_code' => $tranAcquirerAuthCode[$index],
                    'acquirer_message' => $tranAcquirerMessage[$index],
                    'created_at' => $tranCreatedAt[$index]
                ];
                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }

}