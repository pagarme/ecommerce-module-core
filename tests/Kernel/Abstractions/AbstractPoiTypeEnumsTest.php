<?php

namespace Pagarme\Core\Test\Kernel\Abstractions;

use Pagarme\Core\Kernel\Abstractions\AbstractPoiTypeEnums;
use PHPUnit\Framework\TestCase;

class AbstractPoiTypeEnumsTest extends TestCase
{
    /**
     * Concrete anonymous subclass used to access the abstract class members.
     *
     * @return AbstractPoiTypeEnums
     */
    private function getConcreteClass(): string
    {
        return new class extends AbstractPoiTypeEnums {};
    }

    public function testConstantPosHasExpectedValue()
    {
        $this->assertEquals('Pos', AbstractPoiTypeEnums::POS);
    }

    public function testConstantTefHasExpectedValue()
    {
        $this->assertEquals('Tef', AbstractPoiTypeEnums::TEF);
    }

    public function testConstantLinkHasExpectedValue()
    {
        $this->assertEquals('Link', AbstractPoiTypeEnums::LINK);
    }

    public function testConstantTapOnPhoneHasExpectedValue()
    {
        $this->assertEquals('TapOnPhone', AbstractPoiTypeEnums::TAP_ON_PHONE);
    }

    public function testConstantWhatsappPayHasExpectedValue()
    {
        $this->assertEquals('WhatsappPay', AbstractPoiTypeEnums::WHATSAPP_PAY);
    }

    public function testConstantEcommerceHasExpectedValue()
    {
        $this->assertEquals('Ecommerce', AbstractPoiTypeEnums::ECOMMERCE);
    }

    public function testConstantMicroPosHasExpectedValue()
    {
        $this->assertEquals('MicroPos', AbstractPoiTypeEnums::MICRO_POS);
    }

    public function testConstantManualEntryHasExpectedValue()
    {
        $this->assertEquals('ManualEntry', AbstractPoiTypeEnums::MANUAL_ENTRY);
    }

    public function testGetPoiTypesReturnsArray()
    {
        $this->assertIsArray(AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesContainsAllEightTypes()
    {
        $this->assertCount(8, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesContainsPos()
    {
        $this->assertContains(AbstractPoiTypeEnums::POS, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesContainsTef()
    {
        $this->assertContains(AbstractPoiTypeEnums::TEF, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesContainsLink()
    {
        $this->assertContains(AbstractPoiTypeEnums::LINK, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesContainsTapOnPhone()
    {
        $this->assertContains(AbstractPoiTypeEnums::TAP_ON_PHONE, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesContainsWhatsappPay()
    {
        $this->assertContains(AbstractPoiTypeEnums::WHATSAPP_PAY, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesContainsEcommerce()
    {
        $this->assertContains(AbstractPoiTypeEnums::ECOMMERCE, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesContainsMicroPos()
    {
        $this->assertContains(AbstractPoiTypeEnums::MICRO_POS, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesContainsManualEntry()
    {
        $this->assertContains(AbstractPoiTypeEnums::MANUAL_ENTRY, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testGetPoiTypesReturnsExactExpectedArray()
    {
        $expected = [
            'Pos',
            'Tef',
            'Link',
            'TapOnPhone',
            'WhatsappPay',
            'Ecommerce',
            'MicroPos',
            'ManualEntry',
        ];

        $this->assertEquals($expected, AbstractPoiTypeEnums::getPoiTypes());
    }

    public function testIsValidPoiTypeReturnsTrueForPos()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('Pos'));
    }

    public function testIsValidPoiTypeReturnsTrueForTef()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('Tef'));
    }

    public function testIsValidPoiTypeReturnsTrueForLink()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('Link'));
    }

    public function testIsValidPoiTypeReturnsTrueForTapOnPhone()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('TapOnPhone'));
    }

    public function testIsValidPoiTypeReturnsTrueForWhatsappPay()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('WhatsappPay'));
    }

    public function testIsValidPoiTypeReturnsTrueForEcommerce()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('Ecommerce'));
    }

    public function testIsValidPoiTypeReturnsTrueForMicroPos()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('MicroPos'));
    }

    public function testIsValidPoiTypeReturnsTrueForManualEntry()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('ManualEntry'));
    }

    public function testIsValidPoiTypeIsCaseInsensitiveForPos()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('pos'));
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('POS'));
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('pOs'));
    }

    public function testIsValidPoiTypeIsCaseInsensitiveForTef()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('tef'));
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('TEF'));
    }

    public function testIsValidPoiTypeIsCaseInsensitiveForLink()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('link'));
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('LINK'));
    }

    public function testIsValidPoiTypeIsCaseInsensitiveForTapOnPhone()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('taponphone'));
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('TAPONPHONE'));
    }

    public function testIsValidPoiTypeIsCaseInsensitiveForWhatsappPay()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('whatsapppay'));
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('WHATSAPPPAY'));
    }

    public function testIsValidPoiTypeIsCaseInsensitiveForEcommerce()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('ecommerce'));
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('ECOMMERCE'));
    }

    public function testIsValidPoiTypeIsCaseInsensitiveForMicroPos()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('micropos'));
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('MICROPOS'));
    }

    public function testIsValidPoiTypeIsCaseInsensitiveForManualEntry()
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('manualentry'));
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType('MANUALENTRY'));
    }

    public function testIsValidPoiTypeReturnsFalseForUnknownType()
    {
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType('UnknownType'));
    }

    public function testIsValidPoiTypeReturnsFalseForEmptyString()
    {
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType(''));
    }

    public function testIsValidPoiTypeReturnsFalseForNumericString()
    {
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType('123'));
    }

    public function testIsValidPoiTypeReturnsFalseForWhitespaceOnly()
    {
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType('   '));
    }

    public function testIsValidPoiTypeReturnsFalseForPartialMatch()
    {
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType('Po'));
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType('Ec'));
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType('Manual'));
    }

    public function testIsValidPoiTypeReturnsFalseForTypeWithExtraSpaces()
    {
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType(' Pos'));
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType('Pos '));
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType(' Pos '));
    }

    public function testIsValidPoiTypeReturnsFalseForSpecialCharacters()
    {
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType('Pos!'));
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType('@Ecommerce'));
    }

    /**
     * @dataProvider validPoiTypeProvider
     */
    public function testIsValidPoiTypeReturnsTrueForAllDefinedConstants(string $type)
    {
        $this->assertTrue(AbstractPoiTypeEnums::isValidPoiType($type));
    }

    public static function validPoiTypeProvider(): array
    {
        return [
            'POS'          => [AbstractPoiTypeEnums::POS],
            'TEF'          => [AbstractPoiTypeEnums::TEF],
            'LINK'         => [AbstractPoiTypeEnums::LINK],
            'TAP_ON_PHONE' => [AbstractPoiTypeEnums::TAP_ON_PHONE],
            'WHATSAPP_PAY' => [AbstractPoiTypeEnums::WHATSAPP_PAY],
            'ECOMMERCE'    => [AbstractPoiTypeEnums::ECOMMERCE],
            'MICRO_POS'    => [AbstractPoiTypeEnums::MICRO_POS],
            'MANUAL_ENTRY' => [AbstractPoiTypeEnums::MANUAL_ENTRY],
        ];
    }

    /**
     * @dataProvider invalidPoiTypeProvider
     */
    public function testIsValidPoiTypeReturnsFalseForInvalidValues(string $type)
    {
        $this->assertFalse(AbstractPoiTypeEnums::isValidPoiType($type));
    }

    public static function invalidPoiTypeProvider(): array
    {
        return [
            'empty string'    => [''],
            'random string'   => ['RandomValue'],
            'numeric'         => ['999'],
            'leading space'   => [' Pos'],
            'trailing space'  => ['Pos '],
            'partial Pos'     => ['Po'],
            'partial Manual'  => ['Manual'],
            'special chars'   => ['Pos!'],
        ];
    }
}
