<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Recurrence\Repositories\RepetitionRepository;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;

class RepetitionService
{
    /**
     * @var RepetitionRepository
     */
    private $repetitionRepository;

    /**
     * RepetitionService constructor.
     */
    public function __construct()
    {
        $this->repetitionRepository = new RepetitionRepository();
    }

    /**
     * @param $subscriptionRepetitionsId
     * @return AbstractEntity|Repetition|null
     */
    public function getRepetitionById($subscriptionRepetitionsId)
    {
        return $this->repetitionRepository->find($subscriptionRepetitionsId);
    }
}
