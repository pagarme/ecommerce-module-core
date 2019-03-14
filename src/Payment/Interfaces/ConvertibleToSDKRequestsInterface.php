<?php

namespace Mundipagg\Core\Payment\Interfaces;

interface ConvertibleToSDKRequestsInterface
{
    public function convertToSDKRequest();
}