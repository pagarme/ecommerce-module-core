<?php

namespace Mundipagg\Core\Recurrence\Services;

use MundiAPILib\MundiAPIClient;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Factories\PlanFactory;
use Mundipagg\Core\Recurrence\Repositories\PlanRepository;
use MundiAPILib\Models\CreatePlanRequest;

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

    public function create($postData)
    {
        $planFactory = new PlanFactory();

        $postData['status'] = 'ACTIVE';

        $plan = $planFactory->createFromPostData($postData);
        $planRepository = new PlanRepository();
        $this->createPlanAtMundipagg($plan);
        $planRepository->save($plan);

        return;
    }

    public function createPlanAtMundipagg(Plan $plan)
    {
        $secretKey = ''; //$config->getSecretKey()->getValue();
        $password = '';
        $createPlanRequest = $plan->convertToSdkRequest();

        \MundiAPILib\Configuration::$basicAuthPassword = '';

        $mundipaggApi = new MundiAPIClient($secretKey, $password);
        $planController = $mundipaggApi->getPlans();
        $result = $planController->createPlan($createPlanRequest);
        return $result;

    }

    public function findById($id)
    {
        $planRepository = $this->getPlanRepository();
        return $planRepository->find($id);
    }

    public function findByProductId($id)
    {
        $planRepository = $this->getPlanRepository();
        return $planRepository->findByProductId($id);
    }

    public function getPlanRepository()
    {
        return new PlanRepository();
    }

}