<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Kernel\Services\VersionService;
use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class VersionInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        $versionService = new VersionService();

        $info = new \stdClass();

        $info->phpVersion = phpversion();
        $info->moduleVersion = $versionService->getModuleVersion();
        $info->coreVersion = $versionService->getCoreVersion();

        return $info;
    }
}