<?php

namespace Mundipagg\Core\Test\Abstractions;

use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Test\Mock\Concrete\Migrate;
use Mundipagg\Core\Test\Mock\Concrete\PlatformCoreSetup;
use PHPUnit\Framework\TestCase;

abstract class AbstractRepositoryTest extends TestCase
{
    /**
     * @var AbstractRepository
     */
    protected $repo;

    public function setUp()
    {
        parent::setUp();
        PlatformCoreSetup::bootstrap();
        $this->repo = $this->getRepository();
    }

    public function tearDown()
    {
        parent::tearDown();
        $migrate = new Migrate(PlatformCoreSetup::getDatabaseAccessObject());
        $migrate->down();
    }

    abstract public function getRepository();
}