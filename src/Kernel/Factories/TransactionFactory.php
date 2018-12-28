<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Aggregates\Transaction;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\TransactionId;
use Mundipagg\Core\Kernel\ValueObjects\TransactionStatus;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;

class TransactionFactory implements FactoryInterface
{
    public function createFromPostData($postData)
    {
        $transaction = new Transaction;

        $transaction->setMundipaggId(new TransactionId($postData['id']));

        $baseStatus = explode('_', $postData['status']);
        $status = $baseStatus[0];
        for ($i = 1; $i < count($baseStatus); $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        if (!method_exists(TransactionStatus::class, $status)) {
            throw new InvalidParamException(
                "$status is not a valid TransactionStatus!",
                $status
            );
        }
        $transaction->setStatus(TransactionStatus::$status());

        $baseType = explode('_', $postData['transaction_type']);
        $type = $baseType[0];
        for ($i = 1; $i < count($baseType); $i++) {
            $type .= ucfirst(($baseType[$i]));
        }

        if (!method_exists(TransactionType::class, $type)) {
            throw new InvalidParamException(
                "$type is not a valid TransactionType!",
                $type
            );
        }
        $transaction->setTransactionType(TransactionType::$type());

        $transaction->setAmount($postData['amount']);

        return $transaction;
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }
}