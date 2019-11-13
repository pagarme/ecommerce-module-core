<?php

namespace Mundipagg\Core\Test\Kernel\ValueObjects\Id;

use Mundipagg\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class ChargeIdTest extends TestCase
{
    const VALID1 = 'ch_xxxxxxxxxxxxxxxx';
    const VALID2 = 'ch_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anChargeIdShouldAcceptOnlyValidChargeIds()
    {
        $this->doValidStringTest();
    }
}
