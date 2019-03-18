<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects;

use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\NumericString;
use PHPUnit\Framework\TestCase;

class NumericStringTest extends TestCase
{

    const VALID1 = '1234';
    const VALID2 = 1345;

    const INVALID = '13notanumber45';

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Kernel\ValueObjects\NumericString
     *
     * @uses \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function aNumericStringShouldAcceptOnlyNumbers()
    {
        $numericString = new NumericString(self::VALID1);
        $this->assertEquals(self::VALID1, $numericString->getValue());

        $numericString = new NumericString(self::VALID2);
        $this->assertEquals(self::VALID2, $numericString->getValue());


        $this->expectException(InvalidParamException::class);
        $numericString = new NumericString(self::INVALID);
    }

}
