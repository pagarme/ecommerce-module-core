<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Recurrence\Repositories\SubProductRepository;

class SubProductService
{
    public function findByRecurrenceIdAndProductId($recurrenceId, $productId)
    {
        $subProductRepository = $this->getSubProductRepository();
        return $subProductRepository->findByRecurrenceIdAndProductId(
            $recurrenceId,
            $productId
        );
    }

    protected function getSubProductRepository()
    {
        return new SubProductRepository();
    }
}