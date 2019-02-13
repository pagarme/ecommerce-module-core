<?php

namespace Mundipagg\Core\Maintenance\Interfaces;

interface InstallDataSourceInterface
{
    
    public function getFiles();
    public function getIntegrityFilePath();
}