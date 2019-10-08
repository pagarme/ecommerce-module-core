<?php

namespace Mundipagg\Core\Test\Kernel\ValueObjects\Key;

use Mundipagg\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Mundipagg\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class HubAccessTokenKeyTest extends TestCase
{
    const VALID1 = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    const VALID2 = 'yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Key\HubAccessTokenKey
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anHubAccessTokenKeyShouldAcceptOnlyValidHubAccessTokenKeys()
    {
        $this->doValidStringTest();
    }
}
