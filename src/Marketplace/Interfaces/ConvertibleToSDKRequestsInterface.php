<?php

namespace Pagarme\Core\Marketplace\Interfaces;

interface ConvertibleToSDKRequestsInterface
{
    public function convertMainToSDKRequest();
    public function convertSecondaryToSDKRequest();
}
