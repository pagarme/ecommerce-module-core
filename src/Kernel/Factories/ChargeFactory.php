<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;

class ChargeFactory implements FactoryInterface
{

    /**
     * @param array $postData
     * @return Charge
     */
    public function createFromPostData($postData)
    {
        $charge = new Charge;
        $status = $postData['status'];

        $charge->setMundipaggId(new ChargeId($postData['id']));
        $charge->setCode($postData['code']);
        $charge->setAmount($postData['amount']);
        $charge->setPaidAmount($postData['paid_amount']);
        $charge->setStatus(ChargeStatus::$status());

        return $charge;
    }
}