<?php

namespace Mundipagg\Core\Test\Unit\Kernel\ValueObjects;

use Mundipagg\Core\Kernel\ValueObjects\NumericString;
use PHPUnit\Framework\TestCase;

class NumericStringTest extends TestCase
{

    const VALID1 = '1234';
    const VALID2 = 1345;

    const INVALID = '13notanumber45';

    use ValidStringTestTrait;

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
        $this->doValidStringTest();
    }
}
