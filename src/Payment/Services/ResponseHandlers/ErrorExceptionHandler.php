<?php

namespace Mundipagg\Core\Payment\Services\ResponseHandlers;

use Mundipagg\Core\Payment\Interfaces\ResponseHandlerInterface;

final class ErrorExceptionHandler implements ResponseHandlerInterface
{

    public function handle($response)
    {
        $a = 1;
    }
}