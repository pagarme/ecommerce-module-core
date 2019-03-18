<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects\Id;

use Mundipagg\Core\Test\Unit\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class CustomerIdTest extends TestCase
{
    const VALID1 = 'cus_xxxxxxxxxxxxxxxx';
    const VALID2 = 'cus_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anCustomerIdShouldAcceptOnlyValidCustomerIds()
    {
        $this->doValidStringTest();
    }
}
