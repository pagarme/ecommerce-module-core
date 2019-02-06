<?php

namespace Mundipagg\Core\Hub\Commands;

use Exception;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as  MPSetup;
use Mundipagg\Core\Kernel\ValueObjects\Id\GUID;
use Mundipagg\Core\Kernel\ValueObjects\Key\PublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\SecretKey;
use Mundipagg\Core\Kernel\Repositories\ConfigurationRepository;

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

        $configRepo = new ConfigurationRepository();

        $configRepo->save($moduleConfig);
    }
}