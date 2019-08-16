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
        return round($amount / 100, 2);
    }

    /**
     *
     * @param  float $amount
     * @return int
     */
    public function floatToCents($amount)
    {
        return intval(round($amount * 100));
    }
}