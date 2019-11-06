<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Recurrence\Factories\PlanFactory;
use Mundipagg\Core\Recurrence\Repositories\PlanRepository;

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

    public function createPlanAtPlatform($postData)
    {
        $planFactory = new PlanFactory();

        $postData['status'] = 'ACTIVE';
        $postData['plan_id'] = 'plan_xcdsdfsad1234567'; /*@todo Get from Plan creation at Mundipagg*/

        $plan = $planFactory->createFromPostData($postData);
        $planRepository = new PlanRepository();
        $planRepository->save($plan);
        return;
    }

    public function createPlanAtMundipagg()
    {

    }


}