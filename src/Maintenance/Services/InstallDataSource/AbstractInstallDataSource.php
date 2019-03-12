<?php

namespace Mundipagg\Core\Maintenance\Services\InstallDataSource;

use Mundipagg\Core\Maintenance\Interfaces\InstallDataSourceInterface;

abstract class AbstractInstallDataSource implements InstallDataSourceInterface
{
    //@todo $ignoreVendor should be an array of directories to ignore instead of boolean
    protected function scanDirs($dirs, $ignoreVendor = false)
    {
        $files = [];
        foreach($dirs as $dir) {
            if (is_file($dir)) {
                $files[$dir] = $dir;
                continue;
            }
            $foundFiles = scandir($dir);
            if ($foundFiles !== false) {
                foreach ($foundFiles as $foundFile) {
                    if (strlen($foundFile) < 3
                        || (                        $ignoreVendor 
                        && (strpos($dir, 'vendor') !== false
                        || strpos($dir, 'lib') !== false))
                    ) {
                        continue;
                    }

                    $foundFile = $dir . DIRECTORY_SEPARATOR . $foundFile;
                    $foundFile = preg_replace(
                        '/\\' .DIRECTORY_SEPARATOR. '{2,}/',
                        DIRECTORY_SEPARATOR,
                        $foundFile
                    );

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

    abstract protected function getInstallDirs();
}