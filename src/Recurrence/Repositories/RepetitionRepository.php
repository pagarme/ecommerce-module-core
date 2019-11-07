<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Recurrence\Factories\RepetitionFactory;

class RepetitionRepository extends AbstractRepository
{

    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS_SUBSCRIPTION_REPETITION);

        $query = "
            INSERT INTO $table (
                `subsctiption_id`,
                `interval`,
                `interval_count`,
                `discount_type`,
                `discount_value`
            ) VALUES (
                '{$object->getSubscriptionId()}',
                '{$object->getIntervalType()}',
                '{$object->getIntervalCount()}',
                '{$object->getDiscountType()}',
                '{$object->getDiscountValue()}'
            )
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        // TODO: Implement update() method.
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        // TODO: Implement findByMundipaggId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findBySubscriptionId($subscriptionId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS_SUBSCRIPTION_REPETITION);

        $query = "SELECT * FROM $table WHERE subsctiption_id = $subscriptionId";

        $result = $this->db->fetch($query);
        $repetitions = [];

        if ($result->num_rows === 0) {
            return $repetitions;
        }

        $repetitionFactory = new RepetitionFactory();

        foreach ($result->rows as $row) {
            $repetitions[] = $repetitionFactory->createFromDbData($row);
        }

        return $repetitions;
    }
}