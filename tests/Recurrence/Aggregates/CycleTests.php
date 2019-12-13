<?php

namespace Mundipagg\Core\Test\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\ValueObjects\Id\CycleId;
use Mundipagg\Core\Recurrence\Aggregates\Cycle;
use PHPUnit\Framework\TestCase;

class CycleTests extends TestCase
{
    /**
     * @var Cycle
     */
    private $cycle;

    protected function setUp()
    {
        $this->cycle = new Cycle();
    }

    public function testCycleObject()
    {
        $this->cycle->setMundipaggId(new CycleId('cycle_45asDadb8Xd95451'));
        $this->cycle->setId(1);
        $this->cycle->setCycleId(new CycleId('cycle_45asDadb8Xd95451'));
        $this->cycle->setCycleStart(new \DateTime('2019-10-10'));
        $this->cycle->setCycleEnd(new \DateTime('2019-11-11'));

        $this->assertEquals('cycle_45asDadb8Xd95451', $this->cycle->getMundipaggId()->getValue());
        $this->assertEquals(1, $this->cycle->getId());
        $this->assertInstanceOf(\DateTime::class, $this->cycle->getCycleStart());
        $this->assertInstanceOf(\DateTime::class, $this->cycle->getCycleEnd());
    }
}
