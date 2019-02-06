<?php

namespace Mundipagg\Core\Hub\Commands;

use Exception;
use Mundipagg\Core\AbstractMundipaggModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\GatewayId\GUID;
use Mundipagg\Core\Kernel\GatewayKey\PublicKey;
use Mundipagg\Core\Kernel\GatewayKey\SecretKey;
use Mundipagg\Repositories\Configuration as ConfigurationRepository;

class UninstallCommand extends AbstractCommand
{
    public function execute()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        if (!$moduleConfig->isHubEnabled()) {
            throw new Exception("Hub is not installed!");
        }

        $hubKey = $moduleConfig->getSecretKey();
        if (!$hubKey->equals($this->getAccessToken())) {
            throw new Exception("Access Denied.");
        }

        $moduleConfig->setHubInstallId(
            new GUID(null)
        );

        $moduleConfig->setPublicKey(
            new PublicKey(null)
        );
        $moduleConfig->setSecretKey(
            new SecretKey(null)
        );

        $moduleConfig->setTestMode(null);

        $configRepo = new ConfigurationRepository(
            MPSetup::getDatabaseAccessDecorator()
        );

        $configRepo->save($moduleConfig);
    }
}