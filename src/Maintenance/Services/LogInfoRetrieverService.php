<?php

namespace Mundipagg\Core\Maintenance\Services;

use DateTime;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class LogInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        $logInfo = new \stdClass();

        //@todo moduleLogsDirectory can be another, but this feature should be implemented.
        $logInfo->moduleLogsDirectory = AbstractModuleCoreSetup::getLogPath();

        $platformLogPaths = AbstractModuleCoreSetup::getLogPath();

        if (!is_array($platformLogPaths)) {
            $platformLogPaths = [$platformLogPaths];
        }

        $logInfo->platformLogsDirectories = $platformLogPaths;

        $dirs = [];

        foreach ((array)$logInfo as $dir) {
            if (is_array($dir)) {
                foreach ($dir as $subDir) {
                    $dirs[$subDir] = $subDir;
                }
                continue;
            }
            $dirs[$dir] = $dir;
        }
        $dirs = array_values($dirs);

        $files = $this->scanDirs($dirs);

        $files = $this->filterLogFilesByDate($value, $files);

        $requestURI = $_SERVER['REQUEST_URI'];
        foreach ($files as $key => $file) {
            $encoded = base64_encode($file);
            $uriZip =
                ltrim(preg_replace('/log/', 'logDownload=zip:' . $encoded, $requestURI), '/');
            $uriRaw =
                ltrim(preg_replace('/log/', 'logDownload=raw:' . $encoded, $requestURI), '/');

            $donwloadURIs[] = [
                'file' => $file,
                'uriZip' => $uriZip,
                'uriRaw' => $uriRaw
            ];
        }

        $logInfo->files = $files;
        $logInfo->donwloadURIs = $donwloadURIs;

        return $logInfo;
    }

    private function filterLogFilesByDate($dateQuery, $files)
    {
        $dates = explode(':', $dateQuery);

        if (empty($dates)) {
            return $files;
        }
        
        $startDate = $dates[0];
        $startDate = DateTime::createFromFormat('Y-m-d', $startDate);

        $endDate = new DateTime();
        if (isset($dates[1])) {
            $endDate = DateTime::createFromFormat('Y-m-d', $dates[1]);
        }

        $result = [];
        foreach ($files as $file) {

            $matchDate = [];
            preg_match('/\d{4}-\d{2}-\d{2}/', $file, $matchDate);

            if (!isset($matchDate[0])) {
                $result[] = $file;
                continue;
            }

            $fileDate = DateTime::createFromFormat('Y-m-d', $matchDate[0]);

            if ($fileDate >= $startDate && $fileDate <= $endDate) {
                $result[] = $file;
            }
        }

        return $result;
    }

    private function scanDirs($dirs)
    {
        $files = [];
        foreach($dirs as $logDir) {
            $foundFiles = scandir($logDir);
            if ($foundFiles !== false) {
                foreach ($foundFiles as $foundFile) {
                    if (strlen($foundFile) < 3) {
                        continue;
                    }

                    $foundFile = $logDir . DIRECTORY_SEPARATOR . $foundFile;
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

}