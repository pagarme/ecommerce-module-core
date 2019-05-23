<?php

namespace Mundipagg\Core\Payment\Services;

use Mundipagg\Core\Kernel\Interfaces\PlatformCustomerInterface;
use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Payment\Factories\CustomerFactory;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;

class CustomerService
{
    /** @var LogService  */
    protected $logService;

    public function __construct()
    {
        $this->logService = new LogService(
            'CustomerService',
            true
        );
    }

    public function updateCustomerAtMundipagg(PlatformCustomerInterface $platformCustomer)
    {
        $customerFactory = new CustomerFactory();
        $customer = $customerFactory->createFromPlatformData($platformCustomer);

        if ($customer->getMundipaggId() !== null) {
            $this->logService->info("Update customer at Mundipagg: [{$customer->getMundipaggId()}]");
            $apiService = new ApiService();
            $apiService->updateCustomer($customer);
        }
    }

    public function deleteCustomerOnPlatform(PlatformCustomerInterface $platformCustomer)
    {
        $customerFactory = new CustomerFactory();
        $customer = $customerFactory->createFromPlatformData($platformCustomer);

        $customerRepository = new CustomerRepository();
        $customerRepository->deleteByCode($customer->getCode());
    }
}