<?php


namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Factories\TransactionFactory;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Payment\Factories\CustomerFactory;

class ChargeFactory implements FactoryInterface
{

    public function createFromPostData($postData)
    {
        $charge = new Charge();
        $status = $postData['status'];

        $charge->setMundipaggId(new ChargeId($postData['id']));

        $charge->setCode($postData['code']);

        $charge->setAmount($postData['amount']);
        $paidAmount = isset($postData['paid_amount']) ? $postData['paid_amount'] : 0;
        $charge->setPaidAmount($paidAmount);
      //  $charge->setOrderId(new OrderId($postData['order']['id']));

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

        if (!empty($postData['invoice'])) {
            $invoiceFactory = new InvoiceFactory();
            $invoice = $invoiceFactory->createFromPostData($postData['invoice']);
            $charge->setInvoice($invoice);
        }

        return $charge;
    }

    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }
}