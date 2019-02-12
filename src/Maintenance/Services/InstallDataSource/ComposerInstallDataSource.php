<?php

namespace Mundipagg\Core\Maintenance\Services\InstallDataSource;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Maintenance\Interfaces\ModuleInstallTypeInterface;

final class ComposerInstallDataSource
    extends AbstractInstallDataSource
    implements ModuleInstallTypeInterface
{
    private $integrityFilePath;
    private $composerJsonFilePath;

    public function __construct()
    {
        $concreteDir = AbstractModuleCoreSetup::getModuleConcreteDir();

        $this->integrityFilePath = $concreteDir . DIRECTORY_SEPARATOR . 'integrityData';

        $composerJsonFilePath = explode(DIRECTORY_SEPARATOR, $concreteDir);
        array_pop($composerJsonFilePath);

        $this->composerJsonFilePath = implode(DIRECTORY_SEPARATOR, $composerJsonFilePath);
        $this->composerJsonFilePath .= DIRECTORY_SEPARATOR . 'composer.json';
    }

    public function getFiles()
    {
        $files =  $this->scanDirs(
            $this->getInstallDirs(),
            true
        );
        return array_filter(
            $files, function ($file) {
                return
                strpos($file, 'integrityData') === false
                ;
            }
        );
    }

    public function getIntegrityFilePath()
    {
        if (file_exists($this->integrityFilePath)
            && file_exists($this->composerJsonFilePath)
        ) {
            return $this->integrityFilePath;
        }

        return null;
    }

    protected function getInstallDirs()
    {
        $moduleRoot = explode(DIRECTORY_SEPARATOR, $this->composerJsonFilePath);
        array_pop($moduleRoot);
        $moduleRoot = implode(DIRECTORY_SEPARATOR, $moduleRoot);

        $dirs = scandir($moduleRoot);

        $finalDirs = [];

        foreach ($dirs as $dir) {
            if (substr($dir, 0, 1) == '.') {
                continue;
            }
            $finalDir = $moduleRoot . DIRECTORY_SEPARATOR . $dir;
            if (is_dir($finalDir)) {
                $finalDirs[] = $finalDir;
            }
        }

        return $finalDirs;
    }
}