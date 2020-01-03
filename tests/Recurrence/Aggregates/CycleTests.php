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
        $cycleId = new CycleId('cycle_45asDadb8Xd95451');
        $this->cycle->setId(1);
        $this->cycle->setMundipaggId($cycleId);
        $this->cycle->setCycleId($cycleId);
        $this->cycle->setCycleStart(new \DateTime('2019-10-10'));
        $this->cycle->setCycleEnd(new \DateTime('2019-11-11'));

        $this->assertEquals('cycle_45asDadb8Xd95451', $this->cycle->getMundipaggId()->getValue());
        $this->assertEquals(1, $this->cycle->getId());
        $this->assertEquals($cycleId, $this->cycle->getCycleId());
        $this->assertInstanceOf(\DateTime::class, $this->cycle->getCycleStart());
        $this->assertInstanceOf(\DateTime::class, $this->cycle->getCycleEnd());
    }

    public function testReturnCycleObjectSerialized()
    {
        $cycleId = new CycleId('cycle_45asDadb8Xd95451');
        $this->cycle->setId(1);
        $this->cycle->setMundipaggId($cycleId);
        $this->cycle->setCycleId($cycleId);
        $this->cycle->setCycleStart(new \DateTime('2019-10-10'));
        $this->cycle->setCycleEnd(new \DateTime('2019-11-11'));

        $this->assertJson(json_encode($this->cycle));

    }
}
