<?php

namespace Mundipagg\Core\Test\Kernel\Aggregates;

use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Aggregates\Transaction;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Responses\ServiceResponse;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\ValueObjects\Id\TransactionId;
use PHPUnit\Framework\TestCase;
use Mockery;
use Carbon\Carbon;

class ServiceResponseTest extends TestCase
{
    public function testServiceResponseObject()
    {
        $object = new ServiceResponse(true, 'Foi um sucesso', (object)['status' => 200, 'message' => 'ok']);
        $this->assertEquals('Foi um sucesso', $object->getMessage());
        $this->assertInternalType('object', $object->getObject());
        $this->assertInstanceOf(\stdClass::class, $object->getObject());
        $this->assertEquals(true, $object->isSuccess());
    }
}
