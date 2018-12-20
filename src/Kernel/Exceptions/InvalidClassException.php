<?php

namespace Mundipagg\Core\Kernel\Exceptions;

class InvalidClassException extends AbstractMundipaggCoreException
{
    public function __construct($actualClass, $expectedClass)
    {
        $message = "$actualClass is not a $expectedClass!";
        parent::__construct($message, 400, null);
    }
}