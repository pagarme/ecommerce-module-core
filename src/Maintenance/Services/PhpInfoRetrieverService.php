<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class PhpInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        ob_start();
        phpinfo();
        $phpinfoAsString = ob_get_contents();
        ob_get_clean();

        return $phpinfoAsString;
    }
}