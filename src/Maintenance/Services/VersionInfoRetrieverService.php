<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Services\VersionService;
use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class VersionInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        $versionService = new VersionService();

        $info = new \stdClass();

        $info->phpVersion = phpversion();
        $info->platformCoreConcreteClass = MPSetup::get(MPSetup::CONCRETE_MODULE_CORE_SETUP_CLASS);
        $info->moduleVersion = $versionService->getModuleVersion();
        $info->coreVersion = $versionService->getCoreVersion();
        $info->platformVersion = $versionService->getPlatformVersion();

        return $info;
    }
}