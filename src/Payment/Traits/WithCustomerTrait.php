<?php

namespace Mundipagg\Core\Payment\Traits;

use Mundipagg\Core\Payment\Aggregates\Customer;

trait WithCustomerTrait
{
    /** @var null|Customer */
    protected $customer;

    /**
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }
}