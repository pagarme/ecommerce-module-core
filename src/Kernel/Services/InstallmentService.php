<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Mundipagg\Core\Kernel\ValueObjects\Installment;

final class InstallmentService
{
    /**
     *
     * @param  Order|null     $order
     * @param  CardBrand|null $brand
     * @param  null           $value
     * @return Installment[]
     */
    public function getInstallmentsFor(
        Order $order = null,
        CardBrand $brand = null,
        $value = null,
        $config = null
    ) {
        $amount = 0;
        if($order !== null) {
            $platformOrder = $order->getPlatformOrder();
            $amount = $platformOrder->getGrandTotal() * 100;
        }

        if ($value !== null) {
            $amount = $value;
        }

        if ($config == null) {
            $config = MPSetup::getModuleConfiguration();
        }

        $installmentsEnabled = false;
        if (
            method_exists($config, 'isInstallmentsEnabled') &&
            $config->isInstallmentsEnabled()
        ) {
            $installmentsEnabled = true;
        }

        $useDefaultInstallmentsConfig = $this->getUseDefaultInstallments($config);

        $baseBrand = CardBrand::nobrand();
        if ($brand !== null && !$useDefaultInstallmentsConfig) {
            $baseBrand = $brand;
        }

        $cardConfigs = $config->getCardConfigs();

        $brandConfig = null;

        foreach ($cardConfigs as $cardConfig) {
            if ($cardConfig->getBrand()->equals($baseBrand)) {
                $brandConfig = $cardConfig;
                break;
            }
        }

        if ($brandConfig === null) {
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

        if (!$installmentsEnabled) {
            return array_slice($installments, 0, 1);
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

        return $this->filterInstallmentsByMinValue($installments, $brandConfig);
    }

    public function getUseDefaultInstallments($config)
    {
        if ($config == null || $config instanceof AbstractEntity) {
            return MPSetup::getModuleConfiguration()->isInstallmentsDefaultConfig();
        }
        return false;
    }

    public function getLabelFor(Installment $installment)
    {
        $i18n = new LocalizationService();

        $interestLabel = $i18n->getDashboard('without interest');
        if ($installment->getInterest() > 0) {
            $interestLabel = ", " . $i18n->getDashboard(
                'with %.2f%% of interest',
                $installment->getInterest() * 100
            );
        }

        $formattedValue = MPSetup::formatToCurrency(
            $installment->getValue() / 100
        );
        $formattedTotal = MPSetup::formatToCurrency(
            $installment->getTotal() / 100
        );

        $label = $i18n->getDashboard(
            "%dx of %s %s (Total: %s)",
            $installment->getTimes(),
            $formattedValue,
            $interestLabel,
            $formattedTotal
        );

        return $label;
    }

    /**
     *
     * @param  Installment[] $installments
     * @param  CardConfig    $brandConfig
     * @return array
     */
    protected function filterInstallmentsByMinValue(array $installments, CardConfig $brandConfig)
    {
        return array_filter(
            $installments,
            function (Installment $installment) use ($brandConfig) {
                return
                    $installment->getTimes() === 1 ||
                    $installment->getValue() >= $brandConfig->getMinValue();
            }
        );
    }
}