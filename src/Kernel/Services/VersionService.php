<?php

namespace Mundipagg\Core\Kernel\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Kernel\ValueObjects\VersionPair;

final class VersionService
{
    public function getCoreVersion()
    {
        return '1.2.1';
    }

    public function getModuleVersion()
    {
        return AbstractModuleCoreSetup::getModuleVersion();
    }

    public function getVersionPair()
    {
        return new VersionPair(
            $this->getModuleVersion(),
            $this->getCoreVersion()
        );
    }
}