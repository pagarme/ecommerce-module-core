<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Kernel\Abstractions\AbstractPlatformOrderDecorator;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class InfoBuilderService
{

    /**
     *
     * @param  array $query
     * @return string|array
     */
    public function buildInfoFromQueryArray(array $query)
    {

        $orderService = new OrderService();

        $decoratorClass = AbstractModuleCoreSetup::get
        (AbstractModuleCoreSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

        /** @var AbstractPlatformOrderDecorator $platformOrder */
        $platformOrder = new $decoratorClass();
        $platformOrder->loadByIncrementId($query['order']);

        return $orderService->createOrderAtMundipagg($platformOrder);

        $infos = [];
        if (!$this->isTokenValid($query)) {
            return [];
        }

        foreach ($query as $parameter => $value) {
            $infoRetriever = $this->getInfoRetrieverServiceFor($parameter);
            if ($infoRetriever === null) {
                continue;
            }

            $data = $infoRetriever->retrieveInfo($value);
            if (is_string($data)) {
                return $data;
            }
            $infos[$parameter] = $data;
        }
        return $infos;
    }

    /**
     *
     * @param  $parameter
     * @return null|InfoRetrieverServiceInterface
     */
    private function getInfoRetrieverServiceFor($parameter)
    {
        $infoRetrieverServiceClass =
            'Mundipagg\\Core\\Maintenance\\Services\\' .
            ucfirst($parameter) .
            'InfoRetrieverService';

        if (!class_exists($infoRetrieverServiceClass)) {
            return null;
        }

        return new $infoRetrieverServiceClass();
    }


    private function isTokenValid($token)
    {
        if (is_array($token)) {
            if (!isset($token['token'])) {
                return false;
            }
            $token = $token['token'];
        }

        $passedKeyHash = base64_decode($token);

        $moduleConfig = AbstractModuleCoreSetup::getModuleConfiguration();
        $secretKey = $moduleConfig->getSecretKey();

        if ($secretKey === null) {
            return false;
        }

        $secretKeyHash = $this->generateKeyHash($secretKey->getValue());

        return $secretKeyHash === $passedKeyHash;
    }

    public function generateKeyHash($keyValue)
    {
        return hash('sha512', $keyValue);
    }
}