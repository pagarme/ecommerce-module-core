<?php

namespace Mundipagg\Core\Payment\Traits;

use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;

trait WithAmountTrait
{
    /** @var int */
    protected $amount;

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @throws InvalidParamException
     */
    public function setAmount(int $amount)
    {
        if ($amount < 0) {
            throw new InvalidParamException(
                'Amount should be at least 0!',
                $amount
            );
        }
        $this->amount = $amount;
    }
}