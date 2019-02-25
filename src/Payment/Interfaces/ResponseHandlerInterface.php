<?php

namespace Mundipagg\Core\Payment\Interfaces;

interface ResponseHandlerInterface
{
    public function handle($response);
}