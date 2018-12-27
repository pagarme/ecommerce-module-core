<?php

namespace Mundipagg\Core\Kernel\Abstractions;

use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;

abstract class AbstractPlatformOrderDecorator implements PlatformOrderInterface
{
    protected $platformOrder;

    public function addHistoryComment($message)
    {
        $message = 'MP - ' . $message;
        $this->addMPHistoryComment($message);
    }

    public function getPlatformOrder()
    {
        return $this->platformOrder;
    }

    public function payAmount($amount)
    {
        $platformOrder = $this->getPlatformOrder();

        /*
         * @todo this format operations should be made by a currency format service.
         *      But before doing this, check if a decorator can depend on a service.
         */

        $amountInCurrency = number_format($amount / 100, 2);
        $grandTotal = number_format($platformOrder->getGrandTotal(), 2);
        $totalPaid = number_format($platformOrder->getTotalPaid(), 2);
        $totalDue = number_format($platformOrder->getTotalDue(), 2);

        $totalPaid += $amountInCurrency;
        if ($totalPaid > $grandTotal) {
            $totalPaid = $grandTotal;
        }

        $totalDue -= $amountInCurrency;
        if ($totalDue < 0) {
            $totalDue = 0;
        }

        $platformOrder->setTotalPaid($totalPaid);
        $platformOrder->setBaseTotalPaid($totalPaid);
        $platformOrder->setTotalDue($totalDue);
        $platformOrder->setBaseTotalDue($totalDue);

        return $amountInCurrency;
    }

    public function cancelAmount($amount)
    {
        $platformOrder = $this->getPlatformOrder();

        /*
         * @todo this format operations should be made by a currency format service.
         *      But before doing this, check if a decorator can depend on a service.
         */

        $amountInCurrency = number_format($amount / 100, 2);
        $grandTotal = number_format($platformOrder->getGrandTotal(), 2);
        $totalCanceled = number_format($platformOrder->getTotalCanceled(), 2);

        $totalCanceled += $amountInCurrency;
        if ($totalCanceled > $grandTotal) {
            $totalCanceled = $grandTotal;
        }

        $platformOrder->setTotalCanceled($totalCanceled);
        $platformOrder->setBaseTotalCanceled($totalCanceled);

        return $amountInCurrency;
    }

    abstract protected function addMPHistoryComment($message);
}