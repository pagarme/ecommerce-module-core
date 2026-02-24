<?php

namespace Pagarme\Core\Kernel\Abstractions;

abstract class AbstractPoiTypeEnums
{
    const POS = 'Pos';
    const TEF = 'Tef';
    const LINK = 'Link';
    const TAP_ON_PHONE = 'TapOnPhone';
    const WHATSAPP_PAY = 'WhatsappPay';
    const ECOMMERCE = 'Ecommerce';
    const MICRO_POS = 'MicroPos';
    const MANUAL_ENTRY = 'ManualEntry';

    public static function getPoiTypes(): array
    {
        return [
            self::POS,
            self::TEF,
            self::LINK,
            self::TAP_ON_PHONE,
            self::WHATSAPP_PAY,
            self::ECOMMERCE,
            self::MICRO_POS,
            self::MANUAL_ENTRY,
        ];
    }

    public static function isValidPoiType(string $type): bool
    {
        return in_array(strtolower($type), array_map('strtolower', self::getPoiTypes()));
    }
}
