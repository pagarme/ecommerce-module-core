<?php

namespace Mundipagg\Core\Kernel\Exceptions;

class InvalidOperationException extends AbstractMundipaggCoreException
{
    public function construct($message)
    {
        parent::__construct($message, 400);
    }
}