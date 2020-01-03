<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Recurrence\Aggregates\Cycle;
use Mundipagg\Core\Kernel\ValueObjects\Id\CycleId;

class CycleFactory implements FactoryInterface
{
    /**
     * @param array $postData
     * @return AbstractEntity|Cycle
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $cycle = new Cycle();

        $cycle->setCycleId(new CycleId($postData['id']));
        $cycle->setCycleStart(new \DateTime($postData['start_at']));
        $cycle->setCycleEnd(new \DateTime($postData['end_at']));

        return $cycle;
    }

    public function createFromDbData($dbData)
    {
        $cycle = new Cycle();

        $cycle->setCycleId(new CycleId($dbData['id']));
        $cycle->setCycleStart(new \DateTime($dbData['start_at']));
        $cycle->setCycleEnd(new \DateTime($dbData['end_at']));

        return $cycle;
    }
}
