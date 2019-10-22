<?php

namespace Mundipagg\Core\Test\Kernel\Services;

use Mundipagg\Core\Kernel\Services\OrderLogService;
use PHPUnit\Framework\TestCase;

class OrderLogServiceTests extends TestCase
{
    /**
     * @var OrderLogService
     */
    private $orderLogService;

    public function setUp()
    {
        $this->orderLogService = new OrderLogService();
    }
}