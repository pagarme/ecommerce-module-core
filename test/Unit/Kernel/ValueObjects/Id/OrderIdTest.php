<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects\Id;

use Mundipagg\Core\Test\Unit\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class OrderIdTest extends TestCase
{
    const VALID1 = 'or_xxxxxxxxxxxxxxxx';
    const VALID2 = 'or_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Id\OrderId
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anOrderIdShouldAcceptOnlyValidOrderIds()
    {
        $this->doValidStringTest();
    }
}
