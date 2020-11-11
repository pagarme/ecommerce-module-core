<?php

namespace Mundipagg\Core\Kernel\Factories\Configurations;

use Mundipagg\Core\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\PixConfig;

class PixConfigFactory implements FactoryCreateFromDbDataInterface
{
    /**
     * @param object $data
     * @return PixConfig
     */
    public function createFromDbData($data)
    {
        $pixConfig = new PixConfig();

        if (isset($data->enabled)) {
            $pixConfig->setEnabled((bool) $data->enabled);
        }

        if (!empty($data->title)) {
            $pixConfig->setTitle($data->title);
        }

        if (!empty($data->expirationQrCode)) {
            $pixConfig->setExpirationQrCode($data->expirationQrCode);
        }

        if (!empty($data->bankType)) {
            $pixConfig->setBankType(
                $data->bankType
            );
        }

        return $pixConfig;
    }
}
