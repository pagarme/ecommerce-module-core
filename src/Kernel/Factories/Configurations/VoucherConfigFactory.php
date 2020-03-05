<?php

namespace Mundipagg\Core\Kernel\Factories\Configurations;

use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\VoucherConfig;

class VoucherConfigFactory implements FactoryCreateFromDbDataInterface
{
    /**
     * @param array $data
     * @return VoucherConfig
     * @throws InvalidParamException
     */
    public function createFromDbData($data)
    {
        $voucherConfig = new VoucherConfig();

        if (isset($data->enabled)) {
            $voucherConfig->setEnabled((bool) $data->enabled);
        }

        if (!empty($data->cardOperation)) {
            $voucherConfig->setCardOperation($data->cardOperation);
        }

        if (!empty($data->cardStatementDescriptor)) {
            $voucherConfig->setCardStatementDescriptor(
                $data->cardStatementDescriptor
            );
        }

        if (isset($data->saveCards)) {
            $voucherConfig->setSaveCards((bool) $data->saveCards);
        }

        foreach ($data->cardConfigs as $cardConfig) {
            $brand = strtolower($cardConfig->brand);
            $voucherConfig->addCardConfig(
                new CardConfig(
                    $cardConfig->enabled,
                    CardBrand::$brand(),
                    $cardConfig->maxInstallment,
                    $cardConfig->maxInstallmentWithoutInterest,
                    $cardConfig->initialInterest,
                    $cardConfig->incrementalInterest,
                    $cardConfig->minValue
                )
            );
        }

        return $voucherConfig;
    }
}
