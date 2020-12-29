<?php

namespace Mundipagg\Core\Kernel\Interfaces;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

interface FactoryCreateFromDbDataInterface
{
    /**
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData);
}
