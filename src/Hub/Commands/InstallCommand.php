<?php

namespace Mundipagg\Core\Hub\Commands;

use Exception;
use Mundipagg\Core\AbstractMundipaggModuleCoreSetup as MPSetup;
use Mundipagg\Repositories\Configuration as ConfigurationRepository;

class InstallCommand extends AbstractCommand
{
    public function execute()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        if ($moduleConfig->isHubEnabled()) {
            throw new Exception("Hub already installed!");
        }

        $moduleConfig->setHubInstallId($this->getInstallId());

        $moduleConfig->setTestMode(
            $this->getType()->equals(CommandType::Sandbox())
        );

        $moduleConfig->setPublicKey(
            $this->getAccountPublicKey()->getValue()
        );

        $moduleConfig->setSecretKey(
            $this->getAccessToken()->getValue()
        );

        $configRepo = new ConfigurationRepository(
            MPSetup::getDatabaseAccessDecorator()
        );

        $configRepo->save($moduleConfig);
    }
}