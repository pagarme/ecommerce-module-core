<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects\Id;

use Mundipagg\Core\Test\Unit\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class MerchantIdTest extends TestCase
{
    const VALID1 = 'merch_xxxxxxxxxxxxxxxx';
    const VALID2 = 'merch_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Id\MerchantId
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anMerchantIdShouldAcceptOnlyValidMerchantIds()
    {
        $this->doValidStringTest();
    }
}
