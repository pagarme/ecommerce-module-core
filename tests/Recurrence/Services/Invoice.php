<?php

namespace Mundipagg\Core\Test\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Recurrence\Services\InvoiceService;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use PHPUnit\Framework\TestCase;
use Mundipagg\Core\Test\Mock\Concrete\PlatformCoreSetup;

class Invoice extends TestCase
{
    /**
     * @var InvoiceService
     */
    protected $service;

    public function setUp()
    {
        $this->service = \Mockery::mock(InvoiceService::class)->makePartial();

        $logMock = \Mockery::mock(LogService::class);
        $logMock->shouldReceive('info')->andReturn(true);
        $this->service->shouldReceive('getLogService')->andReturn($logMock);

        PlatformCoreSetup::bootstrap();

        parent::setUp();
    }

    public function testCancelShouldNotReturnAnError()
    {
        $apiMock = \Mockery::mock(APIService::class);

        $apiMock->shouldReceive('cancelInvoice')->andReturnTrue();

        $this->service->shouldReceive('getApiService')->andReturn($apiMock);

        $return = $this->service->cancel('in_1234567890123456');

        $expexted = [
            "message" => 'Invoice canceled successfully',
            "code" => 200
        ];

        $this->assertEquals($return, $expexted);
    }

    public function testCancelShouldReturnAnErrorMessage()
    {
        $apiMock = \Mockery::mock(APIService::class);

        $apiMock->shouldReceive('cancelInvoice')->andThrow(
            new \Exception("Can't cancel")
        );

        $this->service->shouldReceive('getApiService')->andReturn($apiMock);

        $return = $this->service->cancel('in_1234567890123456');

        $expexted = [
            "message" => "Can't cancel",
            "code" => 400
        ];

        $this->assertEquals($return, $expexted);
    }
    public function testSetAnIncorrectInvoiceIdInCancelMethodShouldReturnAnErrorMessage()
    {
        $apiMock = \Mockery::mock(APIService::class);
        $apiMock->shouldReceive('cancelInvoice')->andReturnTrue();
        $this->service->shouldReceive('getApiService')->andReturn($apiMock);

        $return = $this->service->cancel('xxxxxxxxx');

        $expexted = [
            "message" => "Invalid value for Mundipagg\Core\Recurrence\ValueObjects\InvoiceIdValueObject! Passed value: xxxxxxxxx",
            "code" => 400
        ];

        $this->assertEquals($return, $expexted);
    }
}