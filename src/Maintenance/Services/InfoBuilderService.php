<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class InfoBuilderService
{
    public function buildInfoFromQueryArray(array $query)
    {
        $infos = [];
        if ($this->isTokenValid($query)) {
            foreach ($query as $parameter => $value) {
                $infoRetriever = $this->getInfoRetrieverServiceFor($parameter);
                if ($infoRetriever !== null) {
                    $infos[$parameter] = $infoRetriever->retrieveInfo($value);
                }
            }
            return $infos;
        }
        return [];
    }

    /**
     * @param $parameter
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