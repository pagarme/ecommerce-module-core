<?php

namespace Mundipagg\Core\Hub\Commands;

use Exception;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Repositories\ConfigurationRepository;

class InstallCommand extends AbstractCommand
{
    public function execute()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        if ($moduleConfig->isHubEnabled()) {
            throw new Exception("Hub already installed!");
        }

        $moduleConfig->setHubInstallId($this->getInstallId());

        $moduleConfig->setPublicKey(
            $this->getAccountPublicKey()
        );

        $moduleConfig->setSecretKey(
            $this->getAccessToken()
        );

        $configRepo = new ConfigurationRepository();

        $configRepo->save($moduleConfig);
    }
}