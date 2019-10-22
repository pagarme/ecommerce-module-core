<?php

namespace Mundipagg\Core\Test\Kernel\Aggregates;

use Mundipagg\Core\Kernel\Aggregates\LogObject;
use PHPUnit\Framework\TestCase;

class LogObjectTests extends TestCase
{
    /**
     * @var LogObject
     */
    private $logObject;

    public function setUp()
    {
        $this->logObject = new LogObject();
    }

    public function testJsonSerialize()
    {
        $this->assertInternalType('object', $this->logObject->jsonSerialize());
        $this->assertInstanceOf(\stdClass::class, $this->logObject->jsonSerialize());
    }
}
