<?php

namespace Mundipagg\Core\Kernel\Exceptions;

class NotFoundException extends AbstractMundipaggCoreException
{
    public function __construct($message)
    {
        parent::__construct($message, 404, null);
    }
}