<?php


namespace Mundipagg\Core\Test\Payment;

use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use PHPUnit\Framework\TestCase;

class CustomerTests extends TestCase
{
    /**
     * @var Customer
     */
    private $customer;

    public function setUp()
    {
        $this->customer = new Customer();
    }

    public function testBuildCustomerObject()
    {
        $this->customer->setCode(2);
        $this->customer->setMundipaggId(new CustomerId('cus_K7dJ521DiETZnjM4'));
        $this->customer->setName("teste teste sobrenome");
        $this->customer->setEmail("teste@teste.com");
        $this->customer->setDocument("76852559017");
        $this->customer->setType(CustomerType::individual());
    }
}
