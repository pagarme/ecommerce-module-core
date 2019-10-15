<?php

namespace Mundipagg\Core\Test\Kernel\ValueObjects\Id;

use Mundipagg\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class TransactionIdTest extends TestCase
{
    const VALID1 = 'tran_xxxxxxxxxxxxxxxx';
    const VALID2 = 'tran_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Id\TransactionId
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anTransactionIdShouldAcceptOnlyValidTransactionIds()
    {
        $this->doValidStringTest();
    }
}
