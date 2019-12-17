<?php

namespace Mundipagg\Core\Recurrence\Services;

use MundiAPILib\MundiAPIClient;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Factories\PlanFactory;
use Mundipagg\Core\Recurrence\Repositories\PlanRepository;
use MundiAPILib\Models\CreatePlanRequest;
use Mundipagg\Core\Recurrence\ValueObjects\PlanId;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

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

        Magento2CoreSetup::bootstrap();

        $config = Magento2CoreSetup::getModuleConfiguration();
        $secretKey = $config->getSecretKey()->getValue();
        $password = '';

        \MundiAPILib\Configuration::$basicAuthPassword = '';

        $this->mundipaggApi = new MundiAPIClient($secretKey, $password);

    }

    public function save($postData)
    {
        $planFactory = new PlanFactory();
        $postData['status'] = 'ACTIVE';

        $plan = $planFactory->createFromPostData($postData);

        $methodName = "createPlanAtMundipagg";
        if ($plan->getMundipaggId() !== null) {
            $methodName = "updatePlanAtMundipagg";
        }

        $result = $this->{$methodName}($plan);
        $planId = new PlanId($result->id);
        $plan->setMundipaggId($planId);

        $planRepository = new PlanRepository();
        $planRepository->save($plan);
    }

    public function createPlanAtMundipagg(Plan $plan)
    {
        $createPlanRequest = $plan->convertToSdkRequest();
        $planController = $this->mundipaggApi->getPlans();
        $result = $planController->createPlan($createPlanRequest);
        return $result;
    }

    public function updatePlanAtMundipagg(Plan $plan)
    {
        $updatePlanRequest = $plan->convertToSdkRequest(true);
        $planController = $this->mundipaggApi->getPlans();
        $result = $planController->updatePlan($plan->getMundipaggId(), $updatePlanRequest);
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

    public function getMundiAPIClient($secretKey, $password)
    {
        return new MundiAPIClient($secretKey, $password);
    }

}