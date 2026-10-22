<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Key;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class HubAccessTokenKeyTest extends TestCase
{
    const VALID1 = 'acs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    const VALID2 = 'acs_yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy';

    const INVALID = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anHubAccessTokenKeyShouldAcceptOnlyValidHubAccessTokenKeys()
    {
        $this->doValidStringTest();
    }

    /**
     * @test
     * @dataProvider invalidValuesProvider
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anHubAccessTokenKeyShouldRejectValuesWithoutAcsPrefix($value)
    {
        $this->expectException(InvalidParamException::class);
        new HubAccessTokenKey($value);
    }

    public function invalidValuesProvider()
    {
        return [
            'null' => [null],
            'empty' => [''],
            'old 64 chars format' => [str_repeat('x', 64)],
            'prefix not at start' => ['x_acs_token'],
            'uppercase prefix' => ['ACS_token'],
            'prefix without underscore' => ['acstoken'],
        ];
    }
}
