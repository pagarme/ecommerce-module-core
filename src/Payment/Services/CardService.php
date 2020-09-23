<?php

namespace Mundipagg\Core\Payment\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;

class CardService
{
    public function getBrandsAvailables(AbstractEntity $config)
    {
        $brandsAvailables = [];
        $cardConfigs = $config->getCardConfigs();

        foreach ($cardConfigs as $cardConfig) {
            if (
                $cardConfig->isEnabled() &&
                !$cardConfig->getBrand()->equals(CardBrand::nobrand())
            ) {
                $brandsAvailables[] = $cardConfig->getBrand()->getName();
            }
        }

        return $brandsAvailables;
    }
}