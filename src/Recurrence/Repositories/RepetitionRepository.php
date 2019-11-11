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
                `subscription_id`,
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
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS_SUBSCRIPTION_REPETITION);

        $query = "
            UPDATE $table SET
                `subscription_id` = '{$object->getSubscriptionId()}',
                `interval` = '{$object->getIntervalType()}',
                `interval_count` = '{$object->getIntervalCount()}',
                `discount_type` = '{$object->getDiscountType()}',
                `discount_value` = '{$object->getDiscountValue()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS_SUBSCRIPTION_REPETITION);

        $query = "DELETE FROM $table WHERE id = {$object->getId()}";

        $this->db->query($query);
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

        $query = "SELECT * FROM $table WHERE subscription_id = $subscriptionId";

        $result = $this->db->fetch($query);
        $repetitions = [];

        if ($result->num_rows === 0) {
            return $repetitions;
        }

        foreach ($result->rows as $row) {
            $repetitionFactory = new RepetitionFactory();
            $repetitions[] = $repetitionFactory->createFromDbData($row);
        }

        return $repetitions;
    }
}