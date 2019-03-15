<?php


namespace Mundipagg\Core\Payment\Services\ResponseHandlers;

use Mundipagg\Core\Kernel\Services\OrderLogService;
use Mundipagg\Core\Payment\Interfaces\ResponseHandlerInterface;

abstract class AbstractResponseHandler implements ResponseHandlerInterface
{
    protected $logService;

    public function __construct()
    {
        $this->logService = new OrderLogService();
    }
}