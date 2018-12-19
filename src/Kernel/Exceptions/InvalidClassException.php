<?php

namespace Mundipagg\Core\Kernel\Exceptions;

class InvalidClassException extends \Exception
{

    public function __construct($actualClass, $expectedClass)
    {
        $message = "$actualClass is not a $expectedClass!";
        parent::__construct($message, 0, null);
    }
}