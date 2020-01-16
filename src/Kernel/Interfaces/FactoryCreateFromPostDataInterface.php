<?php

namespace Mundipagg\Core\Kernel\Interfaces;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

interface FactoryCreateFromPostDataInterface
{
    /**
     *
     * @param  array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData);
}