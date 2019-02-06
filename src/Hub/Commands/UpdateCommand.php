<?php

namespace Mundipagg\Core\Hub\Commands;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as  MPSetup;
use Exception;

class UpdateCommand extends AbstractCommand
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
    }
}