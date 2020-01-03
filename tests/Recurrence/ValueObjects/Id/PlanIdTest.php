<?php

namespace Mundipagg\Core\Test\Recurrence\ValueObjects\Id;

use Mundipagg\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class PlanIdTest extends TestCase
{
    const VALID1 = 'plan_xxxxxxxxxxxxxxxx';
    const VALID2 = 'plan_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Mundipagg\Core\Recurrence\ValueObjects\Id\PlanId
     *
     * @uses   \Mundipagg\Core\Kernel\ValueObjects\AbstractValidString
     * @uses   \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function aPlanIdShouldAcceptOnlyValidChargeIds()
    {
        $this->doValidStringTest();
    }
}
