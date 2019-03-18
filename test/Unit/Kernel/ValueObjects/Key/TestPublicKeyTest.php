<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects\Key;

use Mundipagg\Core\Test\Unit\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class TestPublicKeyTest extends TestCase
{
    const VALID1 = 'pk_test_xxxxxxxxxxxxxxxx';
    const VALID2 = 'pk_test_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Key\TestPublicKey
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anTestPublicKeyShouldAcceptOnlyValidTestPublicKeys()
    {
        $this->doValidStringTest();
    }
}