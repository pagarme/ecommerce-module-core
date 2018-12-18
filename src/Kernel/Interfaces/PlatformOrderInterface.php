<?php

namespace Mundipagg\Core\Kernel\Interfaces;

interface PlatformOrderInterface
{
    public function save();
    public function setState();
    public function setStatus();
    public function addHistoryComment();
    public function setIsCustomerNotified();

}