<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;

class InfoBuilderService
{
    public function buildInfoFromQueryArray(array $query)
    {
        if ($this->isTokenValid($query)) {

        }
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