<?php

namespace Mundipagg\Core\Payment\Services\ResponseHandlers;

final class ErrorExceptionHandler extends AbstractResponseHandler
{
    public function handle($error)
    {
        throw $error;
    }
}