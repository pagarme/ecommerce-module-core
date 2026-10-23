<?php

namespace Pagarme\Core\Test\Kernel\Repositories;

use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Repositories\ConfigurationRepository;
use Pagarme\Core\Test\Abstractions\AbstractRepositoryTest;

class ConfigurationRepositoryTest extends AbstractRepositoryTest
{
    public function getRepository()
    {
        return new ConfigurationRepository();
    }

    public function testShouldPersistPaymentProfileIdWithSingleQuoteAsLiteral()
    {
        $configuration = new Configuration();
        $configuration->setPaymentProfileId("pp'123");

        $this->repo->save($configuration);

        $found = $this->repo->find($configuration->getId());

        $this->assertEquals("pp'123", $found->getPaymentProfileId());
    }

    public function testShouldNotExecuteSqlPayloadInPaymentProfileId()
    {
        $configuration = new Configuration();
        $configuration->setPaymentProfileId("x'); DELETE FROM pagarme_module_core_configuration; --");

        $this->repo->save($configuration);

        $found = $this->repo->find($configuration->getId());

        $this->assertEquals(
            "x'); DELETE FROM pagarme_module_core_configuration; --",
            $found->getPaymentProfileId()
        );
    }
}
