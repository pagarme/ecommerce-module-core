<?php

namespace Mundipagg\Core\Payment\Factories;

use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\Aggregates\Customer;

class CustomerFactory implements FactoryInterface
{
    /**
     *
     * @param  \stdClass $postData
     * @return Customer
     */
    public function createFromPostData($postData)
    {
        $postData = json_decode(json_encode($postData));

        $customer = new Customer();

        $customer->setMundipaggId(
            new CustomerId($postData->id)
        );

        if (!empty($postData->code)) {
            $customer->setCode($postData->code);
        }

        return $customer;
    }

    /**
     *
     * @param  array $dbData
     * @return Customer
     */
    public function createFromDbData($dbData)
    {
        $customer = new Customer;

        $customer->setCode($dbData['code']);
        $customer->setMundipaggId(new CustomerId($dbData['mundipagg_id']));

        return $customer;
    }
}
