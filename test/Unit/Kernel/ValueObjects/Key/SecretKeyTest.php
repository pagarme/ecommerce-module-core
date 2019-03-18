<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects\Key;

use Mundipagg\Core\Test\Unit\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class SecretKeyTest extends TestCase
{
    const VALID1 = 'sk_xxxxxxxxxxxxxxxx';
    const VALID2 = 'sk_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Key\SecretKey
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anSecretKeyShouldAcceptOnlyValidSecretKeys()
    {
        $this->doValidStringTest();
    }
}