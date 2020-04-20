<?php

namespace Mundipagg\Core\Recurrence\Services;

use MundiAPILib\Models\GetPlanItemResponse;
use MundiAPILib\MundiAPIClient;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Factories\PlanFactory;
use Mundipagg\Core\Recurrence\Repositories\PlanRepository;
use MundiAPILib\Models\CreatePlanRequest;
use Mundipagg\Core\Recurrence\ValueObjects\PlanId;
use Mundipagg\Core\Recurrence\ValueObjects\PlanItemId;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class PlanService
{
    public function __construct()
    {
        Magento2CoreSetup::bootstrap();

        $config = Magento2CoreSetup::getModuleConfiguration();
        $secretKey = $config->getSecretKey()->getValue();
        $password = '';

        \MundiAPILib\Configuration::$basicAuthPassword = '';

        $this->mundipaggApi = new MundiAPIClient($secretKey, $password);
    }

    /**
     * @param Plan $plan
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function save(Plan $plan)
    {
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

        try {
            $logService = $this->getLogService();
            $logService->info(
                'Create plan request: ' .
                json_encode($createPlanRequest, JSON_PRETTY_PRINT)
            );

            $result = $planController->createPlan($createPlanRequest);

            $logService->info(
                'Create plan response: ' .
                json_encode($result, JSON_PRETTY_PRINT)
            );

            $this->setItemsId($plan, $result);

            return $result;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }


    }

    public function updatePlanAtMundipagg(Plan $plan)
    {
        $updatePlanRequest = $plan->convertToSdkRequest(true);
        $planController = $this->mundipaggApi->getPlans();

        $this->updateItemsAtMundipagg($plan, $planController);
        $result = $planController->updatePlan($plan->getMundipaggId(), $updatePlanRequest);

        return $result;
    }

    protected function setItemsId(Plan $plan, $result)
    {
        $resultItems = $result->items;
        foreach ($resultItems as $resultItem) {
            $this->updateItems($plan, $resultItem);
        }
    }

    protected function updateItems(Plan $plan, GetPlanItemResponse $resultItem)
    {
        $planItems = $plan->getItems();
        foreach ($planItems as $planItem) {
            if ($this->isItemEqual($planItem, $resultItem)) {
                $planItem->setMundipaggId(
                  new PlanItemId($resultItem->id)
                );
            }
        }
    }

    protected function isItemEqual($planItem, $resultItem)
    {
        return $planItem->getName() == $resultItem->name;
    }

    protected function updateItemsAtMundipagg(Plan $plan, $planController)
    {
        foreach ($plan->getItems() as $item) {
            $planController->updatePlanItem(
                $plan->getMundipaggId(),
                $item->getMundipaggId(),
                $item->convertToSdkRequest()
            );
        }
    }

    public function findById($id)
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->find($id);
    }

    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->findByMundipaggId($mundipaggId);
    }

    public function findAll()
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->listEntities(0, false);
    }

    public function findByProductId($id)
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->findByProductId($id);
    }

    public function delete($id)
    {
        $planRepository = $this->getPlanRepository();
        $plan = $planRepository->find($id);

        if (empty($plan)) {
            throw new \Exception("Plan not found - ID : {$id} ");
        }

        try {
            $planController = $this->mundipaggApi->getPlans();
            $planController->deletePlan($plan->getMundipaggId());
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return $planRepository->delete($plan);
    }

    public function getPlanRepository()
    {
        return new PlanRepository();
    }

    public function getMundiAPIClient($secretKey, $password)
    {
        return new MundiAPIClient($secretKey, $password);
    }

    public function getLogService()
    {
        return new LogService(
            'PlanService',
            true
        );
    }
}
