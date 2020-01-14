<?php


namespace Mundipagg\Core\Recurrence\Services\ResponseHandlers;

use Mundipagg\Core\Kernel\Services\OrderLogService;

abstract class AbstractResponseHandler
{
    protected $logService;

    public function __construct()
    {
        $this->logService = new OrderLogService();
    }
}