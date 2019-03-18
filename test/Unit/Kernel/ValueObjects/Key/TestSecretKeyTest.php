<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects\Key;

use Mundipagg\Core\Test\Unit\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class TestSecretKeyTest extends TestCase
{
    const VALID1 = 'sk_test_xxxxxxxxxxxxxxxx';
    const VALID2 = 'sk_test_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Key\TestSecretKey
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anTestSecretKeyShouldAcceptOnlyValidTestSecretKeys()
    {
        $this->doValidStringTest();
    }
}