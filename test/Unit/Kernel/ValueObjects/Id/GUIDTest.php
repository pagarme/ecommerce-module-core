<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects\Id;

use Mundipagg\Core\Test\Unit\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class GUIDTest extends TestCase
{
    const VALID1 = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
    const VALID2 = 'yyyyyyyy-yyyy-yyyy-yyyy-yyyyyyyyyyyy';
    
    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\Id\GUID
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anGUIDShouldAcceptOnlyValidGUIDs()
    {
        $this->doValidStringTest();
    }
}
