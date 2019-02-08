<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class IntegrityInfoRetrieverService implements InfoRetrieverServiceInterface
{
    private $integrityFilePath;
    
    public function __construct()
    {
        $dir = explode(DIRECTORY_SEPARATOR, __DIR__);
        array_pop($dir);
        $dir = implode (DIRECTORY_SEPARATOR, $dir);


        $this->integrityFilePath = $dir . DIRECTORY_SEPARATOR .
            'Assets' . DIRECTORY_SEPARATOR . 'integrityData';
    }

    public function retrieveInfo($value)
    {
        $integrityInfo = new \stdClass();

        $integrityInfo->core = $this->getIntegrityInfo(
            $this->getCoreAnchorDir(),
            $this->integrityFilePath
        );
        $integrityInfo->module = $this->getIntegrityInfo(
            $this->getModuleAnchorDir(),
            $this->getModuleIntegrityFilePath()
        );

        return $integrityInfo;
    }

    private function getIntegrityInfo($anchorDirectory, $integrityFilePath)
    {
        if ($anchorDirectory === null) {
            return $this->getEmptyIntegrityInfo();
        }

        $anchorDirectory .= DIRECTORY_SEPARATOR . 'src';
        $files = $this->scandirs([$anchorDirectory]);

        $fileHashs = [];
        foreach ($files as $file) {
            $cleanFilename = str_replace(
                $anchorDirectory . DIRECTORY_SEPARATOR,
                '',
                $file
            );
            $fileHashs[$cleanFilename] = $this->generateFileHash($file);
        }

        $integrityData = $this->loadIntegrityData($integrityFilePath);

        $altered = [];
        $removed = [];
        $added = [];
        $processedFiles = 0;
        foreach ($fileHashs as $file => $hash) {
            $fullPath = $anchorDirectory . DIRECTORY_SEPARATOR . $file;

            if ($fullPath == $this->integrityFilePath) {
                continue;
            }

            $processedFiles++;
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

    private function scanDirs($dirs)
    {
        $files = [];
        foreach($dirs as $dir) {
            $foundFiles = scandir($dir);
            if ($foundFiles !== false) {
                foreach ($foundFiles as $foundFile) {
                    if (strlen($foundFile) < 3) {
                        continue;
                    }

                    $foundFile = $dir . DIRECTORY_SEPARATOR . $foundFile;
                    $foundFile = preg_replace('/\\' .DIRECTORY_SEPARATOR. '{2,}/', DIRECTORY_SEPARATOR, $foundFile);

                    if (is_dir($foundFile)) {
                        $files = array_merge(
                            $files,
                            $this->scanDirs([$foundFile])
                        );
                        continue;
                    }

                    $files[$foundFile] = $foundFile;
                }
            }
        }
        return array_values($files);
    }

    
    private function getCoreAnchorDir()
    {
        $currentDir = __DIR__;

        do {
            $currentDir = explode(DIRECTORY_SEPARATOR, $currentDir);
            array_pop($currentDir);
            $currentDir = implode(DIRECTORY_SEPARATOR, $currentDir);

            if (strpos($currentDir, 'ecommerce-module-core') === false) {
                return null;
            }

            $composerJsonFilename =  $currentDir . DIRECTORY_SEPARATOR . 'composer.json';

        } while (!file_exists($composerJsonFilename));

        return $currentDir;
    }

    private function loadIntegrityData($integrityFilePath)
    {
        $data = json_decode(file_get_contents($integrityFilePath), true);
        return $data;
    }

    public function generateCoreIntegrityFile()
    {
        $coreIntegrityData = $this->getIntegrityInfo();

        $assetsDir = explode(DIRECTORY_SEPARATOR, $this->integrityFilePath);
        array_pop($assetsDir);
        $assetsDir = implode (DIRECTORY_SEPARATOR, $assetsDir);

        if (!is_dir($assetsDir)) {
            mkdir($assetsDir);
        }


        file_put_contents(
            $this->integrityFilePath,
            json_encode($coreIntegrityData->files)
        );
    }

    public function getModuleAnchorDir()
    {
        return null;
    }

    public function getModuleIntegrityFilePath()
    {
        return null;
    }
}