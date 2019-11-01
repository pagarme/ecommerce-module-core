<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\LogService;

class PlanService
{
    /** @var LogService  */
    protected $logService;

    public function __construct()
    {
        $this->logService = new LogService(
            'PlanService',
            true
        );
    }
}