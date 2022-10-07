<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use Pagarme\Core\Kernel\Aggregates\LogObject;
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

    public function testjsonSerialize(): string
    {
        $this->assertInternalType('object', $this->logObject->jsonSerialize(): string);
        $this->assertInstanceOf(\stdClass::class, $this->logObject->jsonSerialize(): string);
    }
}
