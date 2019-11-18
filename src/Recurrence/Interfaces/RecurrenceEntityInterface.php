<?php

namespace Mundipagg\Core\Recurrence\Interfaces;

interface RecurrenceEntityInterface
{
    public function getRecurrenceType();
    public function getId();
    public function convertToSdkRequest();
}