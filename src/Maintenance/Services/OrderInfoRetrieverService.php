<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class OrderInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
       $orderInfo = new \stdClass();

       $orderInfo->core = $this->getCoreOrderInfo($value);
       $orderInfo->platform = $this->getPlatformOrderInfo($value);

       return $orderInfo;
    }


    private function getPlatformOrderInfo($orderIncrementId)
    {
        $platformOrderClass = MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);
        /** @var PlatformOrderInterface $platformOrder */
        $platformOrder = new $platformOrderClass();
        $platformOrder->loadByIncrementId($orderIncrementId);

        $platformOrderInfo = new \stdClass();

        $platformOrderInfo->order = $platformOrder->getData();

        $platformOrderInfo->history = $platformOrder->getHistoryCommentCollection();

        $platformOrderInfo->transaction = 'platform ordertransaction data';
        $platformOrderInfo->payments = 'platform order payments data';
        $platformOrderInfo->invoices = 'platform order invoice data';

        return $platformOrderInfo;
    }

    private function getCoreOrderInfo($orderIncrementId)
    {
        return 'core';
    }
}