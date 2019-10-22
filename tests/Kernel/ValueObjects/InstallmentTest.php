<?php

namespace Mundipagg\Core\Test\Kernel\ValueObjects;

use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\Installment;
use PHPUnit\Framework\TestCase;

class InstallmentTest extends TestCase
{
    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Installment
     *
     * @uses \Mundipagg\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aInstallmentShouldBeComparable()
    {
        $installment11 = new Installment(1,1,1);
        $installment12 = new Installment(1,1,1);

        $installment2 = new Installment(1,2,1);

        $this->assertTrue($installment11->equals($installment12));
        $this->assertTrue($installment12->equals($installment11));
        $this->assertFalse($installment11->equals($installment2));
    }

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Installment
     */
    public function aInstallmentShouldBeJsonSerializable()
    {
        $base = new \stdClass();
        $base->times = 2;
        $base->baseTotal = 25;
        $base->interest = 0.3;
        $base->total = 33;
        $base->value = 17;

        $installment = new Installment(
            $base->times,
            $base->baseTotal,
            $base->interest
        );

        $json = json_encode($installment);
        $expected = json_encode($base);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Installment::setTimes()
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\Installment
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function installmentTimesShouldBeBetween1And12()
    {
        for ($times = 1; $times <= 12; $times++) {
            $installment = new Installment(
                $times,
                1,
                0
            );

            $this->assertEquals($times, $installment->getTimes());
        }

        $tries = 20;
        $hits = 0;
        for($try = 0; $try < $tries; $try++) {
            try {
                $installment = new Installment(
                    rand(13, 100000),
                    1,
                    0
                );
            } catch (InvalidParamException $e) {
                $hits++;
            }

            try {
                $installment = new Installment(
                    rand(-100000, 0),
                    1,
                    0
                );
            } catch (InvalidParamException $e) {
                $hits++;
            }
        }

        $this->assertEquals($tries*2, $hits);
    }

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Installment::setBaseTotal()
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\Installment
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function installmentBaseTotalShouldBeAtLeast0()
    {
        $valid = new Installment(1, 0, 0);
        $this->assertEquals(0, $valid->getBaseTotal());

        $valid= new Installment(1, 1, 0);
        $this->assertEquals(1, $valid->getBaseTotal());

        $this->expectException(InvalidParamException::class);
        $invalid = new Installment(1, -1, 0);
    }

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Installment::setInterest()
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\Installment
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function installmentInterestShouldBeAtLeast0()
    {
        $valid = new Installment(1, 0, 0);
        $this->assertEquals(0, $valid->getInterest());

        $valid= new Installment(1, 0, 1);
        $this->assertEquals(1, $valid->getInterest());

        $this->expectException(InvalidParamException::class);
        $invalid = new Installment(1, 0, -1);
    }

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Installment::getTimes
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Installment::getBaseTotal
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Installment::getInterest
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\Installment
     */
    public function basePropertyGettersShouldReturnCorrectValues()
    {
        $valid = new Installment(1, 2, 3);

        $this->assertEquals(1, $valid->getTimes());
        $this->assertEquals(2, $valid->getBaseTotal());
        $this->assertEquals(3, $valid->getInterest());
    }
}
