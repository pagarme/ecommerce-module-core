<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;

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
        return intval(round($amount * 100, 2));
    }

    public function removeSeparators($amount)
    {
        return str_replace(
            ['.', ','],
            "",
            $amount
        );
    }
}