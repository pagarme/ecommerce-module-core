<?php

namespace Mundipagg\Core\Kernel\Interfaces;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

interface SensibleDataInterface
{
    /**
     *
     * @param  string
     * @return string
     */
    public function hideSensibleData($string);
}