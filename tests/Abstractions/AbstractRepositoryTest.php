<?php

namespace Mundipagg\Core\Test\Abstractions;

use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;

abstract class AbstractRepositoryTest extends AbstractSetupTest
{
    /**
     * @var AbstractRepository
     */
    protected $repo;

    public function setUp()
    {
        parent::setUp();
        $this->repo = $this->getRepository();
    }

    abstract public function getRepository();
}