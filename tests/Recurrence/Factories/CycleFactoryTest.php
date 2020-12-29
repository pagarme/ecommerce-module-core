<?php

namespace Mundipagg\Core\Test\Recurrence\Factories;

use Mundipagg\Core\Recurrence\Aggregates\Cycle;
use Mundipagg\Core\Recurrence\Factories\CycleFactory;
use PHPUnit\Framework\TestCase;

class CycleFactoryTest extends TestCase
{
    public function testShouldCreateAnCycleFromPostData()
    {
        $factory = new CycleFactory();

        $postData = [
            'id' => 'cycle_xxxxxxxxxxxxxxxx',
            'start_at' => '2020-01-03',
            'end_at' => '2020-02-03',
        ];

        $cycle = $factory->createFromPostData($postData);
        $this->assertInstanceOf(Cycle::class, $cycle);
    }

    public function testShouldCreateAnCycleFromDbData()
    {
        $factory = new CycleFactory();

        $dbData = [
            'id' => 'cycle_xxxxxxxxxxxxxxxx',
            'start_at' => '2020-01-03',
            'end_at' => '2020-02-03',
        ];

        $cycle = $factory->createFromDbData($dbData);
        $this->assertInstanceOf(Cycle::class, $cycle);
    }
}