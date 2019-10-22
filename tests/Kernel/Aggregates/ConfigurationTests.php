<?php


namespace Mundipagg\Core\Test\Kernel\Aggregates;

use Mundipagg\Core\Kernel\Aggregates\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTests extends TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function setUp()
    {
        $this->configuration = new Configuration();
    }

    public function testsIsEnabled()
    {
        $this->configuration->setEnabled(true);
        $this->assertInternalType('bool', $this->configuration->isEnabled());
        $this->assertEquals(true, $this->configuration->isEnabled());
    }

    public function testsIsUnabled()
    {
        $this->configuration->setEnabled(false);
        $this->assertInternalType('bool', $this->configuration->isEnabled());
        $this->assertEquals(false, $this->configuration->isEnabled());
    }
}
