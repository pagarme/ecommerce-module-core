<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\PoiType;
use PHPUnit\Framework\TestCase;

class PoiTypeTest extends TestCase
{
    protected $validTypes = [
        ['method' => 'pos',          'value' => 'Pos'],
        ['method' => 'tef',          'value' => 'Tef'],
        ['method' => 'link',         'value' => 'Link'],
        ['method' => 'tapOnPhone',   'value' => 'TapOnPhone'],
        ['method' => 'whatsappPay',  'value' => 'WhatsappPay'],
        ['method' => 'ecommerce',    'value' => 'Ecommerce'],
        ['method' => 'microPos',     'value' => 'MicroPos'],
        ['method' => 'manualEntry',  'value' => 'ManualEntry'],
    ];

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     */
    public function aPoiTypeShouldBeComparable()
    {
        $ecommerce1 = PoiType::ecommerce();
        $ecommerce2 = PoiType::ecommerce();
        $pos        = PoiType::pos();

        $this->assertTrue($ecommerce1->equals($ecommerce2));
        $this->assertFalse($ecommerce1->equals($pos));
        $this->assertFalse($ecommerce2->equals($pos));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function aPoiTypeShouldBeJsonSerializable()
    {
        $ecommerce = PoiType::ecommerce();

        $json     = json_encode($ecommerce);
        $expected = json_encode(PoiType::ECOMMERCE);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function aPoiTypeShouldReturnItsTypeViaGetType()
    {
        foreach ($this->validTypes as $typeData) {
            $method   = $typeData['method'];
            $expected = $typeData['value'];

            $poiType = PoiType::$method();

            $this->assertEquals($expected, $poiType->getType());
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function allPoiTypeFactoryMethodsShouldInstantiateCorrectly()
    {
        foreach ($this->validTypes as $typeData) {
            $method   = $typeData['method'];
            $expected = $typeData['value'];

            $poiType = PoiType::$method();

            $this->assertInstanceOf(PoiType::class, $poiType);
            $this->assertEquals($expected, $poiType->getType());
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function getAllShouldReturnAllExpectedTypes()
    {
        $expectedTypes = array_column($this->validTypes, 'value');
        $allTypes      = PoiType::getAll();

        $this->assertCount(count($expectedTypes), $allTypes);

        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $allTypes);
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function isValidShouldReturnTrueForAllDefinedTypes()
    {
        foreach (PoiType::getAll() as $type) {
            $this->assertTrue(
                PoiType::isValid($type),
                "Expected isValid() to return true for '{$type}'"
            );
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function isValidShouldBeCaseInsensitive()
    {
        $this->assertTrue(PoiType::isValid('ecommerce'));
        $this->assertTrue(PoiType::isValid('ECOMMERCE'));
        $this->assertTrue(PoiType::isValid('EcOmMeRcE'));
        $this->assertTrue(PoiType::isValid('pos'));
        $this->assertTrue(PoiType::isValid('POS'));
        $this->assertTrue(PoiType::isValid('taponphone'));
        $this->assertTrue(PoiType::isValid('TAPONPHONE'));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function isValidShouldReturnFalseForInvalidType()
    {
        $this->assertFalse(PoiType::isValid('InvalidType'));
        $this->assertFalse(PoiType::isValid(''));
        $this->assertFalse(PoiType::isValid('ecommerce2'));
        $this->assertFalse(PoiType::isValid('pos pos'));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function normalizeShouldReturnCanonicalCasingForValidInput()
    {
        $this->assertEquals('Ecommerce',  PoiType::normalize('ecommerce'));
        $this->assertEquals('Ecommerce',  PoiType::normalize('ECOMMERCE'));
        $this->assertEquals('Ecommerce',  PoiType::normalize('EcOmMeRcE'));
        $this->assertEquals('TapOnPhone', PoiType::normalize('taponphone'));
        $this->assertEquals('TapOnPhone', PoiType::normalize('TAPONPHONE'));
        $this->assertEquals('MicroPos',   PoiType::normalize('micropos'));
        $this->assertEquals('ManualEntry', PoiType::normalize('MANUALENTRY'));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function normalizeShouldReturnNullForInvalidInput()
    {
        $this->assertNull(PoiType::normalize('InvalidType'));
        $this->assertNull(PoiType::normalize(''));
        $this->assertNull(PoiType::normalize('ecommerce2'));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     */
    public function defaultConstantShouldBeEcommerce()
    {
        $this->assertEquals(PoiType::ECOMMERCE, PoiType::DEFAULT);
        $this->assertEquals('Ecommerce', PoiType::DEFAULT);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\PoiType
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     */
    public function aInvalidPoiTypeShouldNotBeInstantiable()
    {
        $poiTypeClass    = PoiType::class;
        $invalidPoiType  = PoiType::ECOMMERCE . PoiType::ECOMMERCE;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            "Call to undefined method {$poiTypeClass}::{$invalidPoiType}()"
        );

        PoiType::$invalidPoiType();
    }
}
