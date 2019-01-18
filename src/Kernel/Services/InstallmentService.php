<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\ValueObjects\Installment;

final class InstallmentService
{


    /**
     * @param Order|null $order
     * @param CardBrand|null $brand
     * @param null $value
     * @return Installment[]
     */
    public function getInstallmentsFor(
        Order $order = null,
        CardBrand $brand = null,
        $value = null
    )
    {
        $amount = 0;
        if($order !== null) {
            $platformOrder = $order->getPlatformOrder();
            $amount = $platformOrder->getGrandTotal() * 100;
        }

        if ($value !== null)
        {
            $amount = $value;
        }

        $baseBrand = CardBrand::nobrand();
        if ($brand !== null)
        {
            $baseBrand = $brand;
        }

        $cardConfigs = MPSetup::getModuleConfiguration()->getCardConfigs();

        $brandConfig = null;

        foreach ($cardConfigs as $cardConfig)
        {
            if ($cardConfig->getBrand()->equals($baseBrand)) {
                $brandConfig = $cardConfig;
                break;
            }
        }

        if ($brandConfig === null)
        {
            return [];
        }

        $installments = [];
        for (
            $i = 1;
            $i <= $brandConfig->getMaxInstallmentWithoutInterest();
            $i++
        ) {
            $installments[] = new Installment($i, $amount, 0);
        }

        for (
            $i = $brandConfig->getMaxInstallmentWithoutInterest() + 1,
            $interestCicle = 0;
            $i <= $brandConfig->getMaxInstallment();
            $i++,
            $interestCicle++
        ) {
            $interest = $brandConfig->getInitialInterest();
            $interest += $brandConfig->getIncrementalInterest() * $interestCicle;
            $installments[] = new Installment($i, $amount, $interest / 100);
        }

        return $installments;
    }

    public function getLabelFor(Installment $installment)
    {
        $i18n = new LocalizationService();
        $currencySymbol = 'R$'; //@todo get correct currency symbol.

        $interestLabel = $i18n->getDashboard('without interest');
        if ($installment->getInterest() > 0) {
            $interestLabel = ", " . $i18n->getDashboard(
                'with %.2f%% of interest',
                $installment->getInterest() * 100

            );
        }

        //@todo get correct currency format.

        $label = $i18n->getDashboard(
            "%dx of %s%.2f %s (Total: %s%.2f)",
            $installment->getTimes(),
            $currencySymbol,
            $installment->getValue() / 100,
            $interestLabel,
            $currencySymbol,
            $installment->getTotal() / 100
        );

        return $label;
    }
}