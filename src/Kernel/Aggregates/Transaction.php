<?php

namespace Mundipagg\Core\Kernel\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\TransactionStatus;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;

final class Transaction extends AbstractEntity
{
    /**
     * @var TransactionType
     */
    private $transactionType;
    /** @var int */
    private $amount;
    /** @var TransactionStatus */
    private $status;

    /**
     * @return TransactionType
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * @param TransactionType $transactionType
     */
    public function setTransactionType(TransactionType $transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount)
    {
        if ($amount < 0) {
            throw new InvalidParamException(
                'Amount should be greater than or equal to 0!',
                $amount
            );
        }

        $this->amount = $amount;
    }

    /**
     * @return TransactionStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param TransactionStatus $status
     */
    public function setStatus(TransactionStatus $status)
    {
        $this->status = $status;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {

    }
}