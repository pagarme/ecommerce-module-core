<?php

namespace Mundipagg\Core\Recurrence\Interfaces;

interface SubProductEntityInterface
{
    public function getId();
    public function convertToSdkRequest();
}