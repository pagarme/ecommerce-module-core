<?php

namespace Mundipagg\Core\Test\Abstractions;

use Mundipagg\Core\Test\Mock\Concrete\Migrate;
use Mundipagg\Core\Test\Mock\Concrete\PlatformCoreSetup;
use PHPUnit\Framework\TestCase;

abstract class AbstractSetupTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        PlatformCoreSetup::bootstrap();
    }

    public function tearDown()
    {
        parent::tearDown();
        $migrate = new Migrate(PlatformCoreSetup::getDatabaseAccessObject());
        $migrate->down();
    }
}