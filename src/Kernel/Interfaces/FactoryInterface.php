<?php

namespace Mundipagg\Core\Kernel\Interfaces;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

interface FactoryInterface
{
    /**
     *
     * @param  array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData);
}