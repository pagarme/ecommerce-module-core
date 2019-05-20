<?php

namespace Mundipagg\Core\Payment\Factories;

use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\Interfaces\PlatformCustomerInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\ValueObjects\CustomerPhones;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use Mundipagg\Core\Payment\ValueObjects\Phone;

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

    public function createFromJson($json)
    {
        $data = json_decode($json);

        $customer = new Customer;

        $customer->setName($data->name);
        $customer->setEmail($data->email);
        $customer->setDocument($data->document);
        $customer->setType(CustomerType::individual());

        $homePhone = new Phone(
            substr($data->homePhone, 0, 2),
            substr($data->homePhone, 2, 2),
            substr($data->homePhone, 4)
        );

        $mobilePhone = new Phone(
            substr($data->mobilePhone, 0, 2),
            substr($data->mobilePhone, 2, 2),
            substr($data->mobilePhone, 4)
        );

        $customer->setPhones(
            CustomerPhones::create([$homePhone, $mobilePhone])
        );

        $addressFactory = new AddressFactory();
        $customer->setAddress($addressFactory->createFromJson($json));

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

    public function createFromPlatformData(PlatformCustomerInterface $platformData)
    {
        $customer = new Customer;

        if ($platformData->getMundipaggId()) {
            $customer->setMundipaggId(
                new CustomerId($platformData->getMundipaggId())
            );
        }

        $customer->setCode($platformData->getCode());
        $customer->setName($platformData->getName());
        $customer->setEmail($platformData->getEmail());
        $customer->setDocument($platformData->getDocument());
        $customer->setType($platformData->getType());
        /** @todo set address and phones */

        return $customer;
    }
}
