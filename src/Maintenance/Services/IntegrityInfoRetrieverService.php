<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;
use Mundipagg\Core\Maintenance\Interfaces\InstallDataSourceInterface;
use Mundipagg\Core\Maintenance\Interfaces\ModuleInstallTypeInterface;
use Mundipagg\Core\Maintenance\Services\InstallDataSource\CoreInstallDataSource;

class IntegrityInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        $integrityInfo = new \stdClass();

        $integrityInfo->core = $this->getIntegrityInfo(
            new CoreInstallDataSource()
        );

        $moduleInstall = $this->getModuleInstallDataSource();

        $integrityInfo->module = $this->getEmptyIntegrityInfo();

        if ($moduleInstall !== null) {
            $integrityInfo->module = $this->getIntegrityInfo(
                $moduleInstall
            );
        }

        return $integrityInfo;
    }

    /**
     *
     * @return InstallDataSourceInterface|null
     */
    public function getModuleInstallDataSource()
    {
        $installDataSourcesDir = __DIR__ . DIRECTORY_SEPARATOR . 'InstallDataSource';

        $classes = scandir($installDataSourcesDir);
        array_walk(
            $classes, function (&$class) {
                $class = str_replace('InstallDataSource.php', '', $class);
            }
        );
        $classes = array_filter(
            $classes, function ($item) {
                return strlen($item) > 2;
            }
        );

        $validInstallTypes = [];
        $namespace = __NAMESPACE__ . '\\InstallDataSource';
        foreach ($classes as $class) {

            $installClass =  $namespace . '\\' . $class . 'InstallDataSource';
            $implements = class_implements($installClass);
            if (in_array(ModuleInstallTypeInterface::class, $implements)) {
                $validInstallTypes[] = $installClass;
            }
        }

        $integrityFilePath = null;

        foreach ($validInstallTypes as $installTypeClass) {
            /**
             *
 * @var InstallDataSourceInterface $install 
*/
            $install = new $installTypeClass;
            $integrityFilePath = $install->getIntegrityFilePath();
            if ($integrityFilePath !== null) {
                return $install;
            }
        }

        return null;
    }

    private function getIntegrityInfo(InstallDataSourceInterface $dataInstallSource)
    {
        $files = $dataInstallSource->getFiles();

        $rootDir = $this->detectRootDir($files);

        $fileHashs = [];
        foreach ($files as $file) {
            $cleanFilename = str_replace(
                $rootDir,
                '',
                $file
            );
            $fileHashs[$cleanFilename] = $this->generateFileHash($file);
        }

        $itegrityFilePath = $dataInstallSource->getIntegrityFilePath();

        $integrityData = $this->loadIntegrityData($itegrityFilePath);

        $altered = [];
        $removed = [];
        $added = [];
        $processedFiles = 0;
        foreach ($fileHashs as $file => $hash) {
            $fullPath = $rootDir . $file;
            $processedFiles++;

            if ($fullPath == $itegrityFilePath) {
                continue;
            }

            if (!file_exists($fullPath)) {
                $removed[$file] = $hash;
                continue;
            }

            if (!isset($integrityData[$file])) {
                $added[$file] = $hash;
                continue;
            }

            if($integrityData[$file] != $fileHashs[$file] ) {
                $altered[$file] = $hash;
                continue;
            }
        }

        $integrityInfo = new \stdClass();

        $integrityInfo->altered = $altered;
        $integrityInfo->removed = $removed;
        $integrityInfo->added = $added;
        $integrityInfo->total = [
            'altered' => count($altered),
            'removed' => count($removed),
            'added' => count($added),
            'files' => $processedFiles,
            'reference' => count($integrityData),
        ];
        $integrityInfo->files = $fileHashs;
        $integrityInfo->reference = $integrityData;

        return $integrityInfo;

    }

    private function getEmptyIntegrityInfo()
    {
        $emptyIntegrityInfo = new \stdClass();

        $emptyIntegrityInfo->altered = [];
        $emptyIntegrityInfo->removed = [];
        $emptyIntegrityInfo->added = [];
        $emptyIntegrityInfo->total = [
            'altered' => 0,
            'removed' => 0,
            'added' => 0,
            'files' => 0,
            'reference' => 0
        ];
        $emptyIntegrityInfo->files = [];
        $emptyIntegrityInfo->reference = [];

        return $emptyIntegrityInfo;
    }

    private function generateFileHash($filename)
    {
        return md5_file($filename);
    }
    
    private function loadIntegrityData($integrityFilePath)
    {
        $data = [];

        if (strlen($integrityFilePath) > 0) {
            $data = json_decode(file_get_contents($integrityFilePath), true);
        }

        return $data;
    }

    private function detectRootDir($files)
    {
        $dirCount = [];
        foreach ($files as $file) {
            $explodedPath = explode(DIRECTORY_SEPARATOR, $file);

            array_pop($explodedPath);

            foreach ($explodedPath as $position => $part)
            {
                if (!isset($dirCount[$position])) {
                    $dirCount[$position] = [];
                }
                if (!isset($dirCount[$position][$part])) {
                    $dirCount[$position][$part] = 0;
                }
                $dirCount[$position][$part]++;
            }
        }
        $fileCount = count($files);
        $dirCount = array_filter(
            $dirCount, function ($dir) use ($fileCount) {
                return count($dir) == 1 && end($dir) == $fileCount;
            }
        );

        $rootDir = '';
        foreach ($dirCount as $part) {
            $part = array_keys($part);
            $part = end($part);
            $rootDir .= $part . DIRECTORY_SEPARATOR;
        }

        return $rootDir;
    }

    public function generateCoreIntegrityFile()
    {
        $dataSource = new CoreInstallDataSource();
        $files = $dataSource->getFiles();
        $integrityFilePath = $dataSource->getIntegrityFilePath();

        $rootDir = $this->detectRootDir($files);

        $fileHashs = [];
        foreach ($files as $file) {
            $cleanFilename = str_replace(
                $rootDir,
                '',
                $file
            );
            $fileHashs[$cleanFilename] = $this->generateFileHash($file);
        }

        $integrityData = json_encode($fileHashs);
        file_put_contents($integrityFilePath, $integrityData);
    }
}