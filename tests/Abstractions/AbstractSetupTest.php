<?php

namespace Pagarme\Core\Test\Abstractions;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Test\Mock\Concrete\Migrate;
use Pagarme\Core\Test\Mock\Concrete\PlatformCoreSetup;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

abstract class AbstractSetupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->resetModuleCoreSetupSingleton();
        PlatformCoreSetup::bootstrap();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $migrate = new Migrate(PlatformCoreSetup::getDatabaseAccessObject());
        $migrate->down();
        $this->resetModuleCoreSetupSingleton();
    }

    /**
     * Resets the AbstractModuleCoreSetup singleton so that bootstrap()
     * re-initializes on the next setUp(), avoiding stale state after
     * the database is torn down between tests.
     */
    private function resetModuleCoreSetupSingleton(): void
    {
        $reflection = new ReflectionClass(AbstractModuleCoreSetup::class);

        foreach (['instance', 'config', 'moduleConfig', 'moduleVersion', 'platformVersion', 'logPath', 'platformRoot', 'moduleConcreteDir'] as $prop) {
            $property = $reflection->getProperty($prop);
            $property->setAccessible(true);
            $property->setValue(null, null);
        }
    }
}
