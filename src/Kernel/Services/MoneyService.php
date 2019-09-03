<?php

namespace Mundipagg\Core\Kernel\Services;

final class MoneyService
{
    /**
     *
     * @param  int $amount
     * @return float
     */
    public function centsToFloat($amount)
    {
        if (!is_numeric($amount)) {
            throw new InvalidParamException("Amount should be an integer!", $amount);
        }

        return round($amount / 100, 2);
    }

    /**
     *
     * @param  float $amount
     * @return int
     */
    public function floatToCents($amount)
    {
        $amount = (float) $amount;
        if (!is_float($amount)) {
            throw new InvalidParamException("Amount should be a float!", $amount);
        }

        return intval(round($amount * 100));
    }
}