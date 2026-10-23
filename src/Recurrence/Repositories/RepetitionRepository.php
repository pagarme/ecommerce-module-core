<?php

namespace Pagarme\Core\Recurrence\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Factories\RepetitionFactory;

class RepetitionRepository extends AbstractRepository
{

    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);

        $query = "
            INSERT INTO $table (
                `subscription_id`,
                `interval`,
                `interval_count`,
                `recurrence_price`,
                `cycles`
            ) VALUES (
                :subscription_id,
                :interval,
                :interval_count,
                :recurrence_price,
                :cycles
            )
        ";

        return $this->db->query($query, $this->bindings($object));
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);

        $query = "
            UPDATE $table SET
                `subscription_id` = :subscription_id,
                `interval` = :interval,
                `interval_count` = :interval_count,
                `recurrence_price` = :recurrence_price,
                `cycles` = :cycles
            WHERE id = :id
        ";

        $this->db->query($query, $this->bindings($object) + ['id' => $object->getId()]);
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);

        $query = "DELETE FROM $table WHERE id = :id";

        $this->db->query($query, ['id' => $object->getId()]);
    }

    public function deleteBySubscriptionId($subscriptionProductId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);
        $subscriptionProductId = filter_var($subscriptionProductId, FILTER_SANITIZE_NUMBER_INT);
        $query = "DELETE FROM $table WHERE subscription_id = :subscription_id";

        $this->db->query($query, ['subscription_id' => $subscriptionProductId]);
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);
        $objectId = filter_var($objectId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE id = :id";

        $result = $this->db->fetch($query, ['id' => $objectId]);

        if ($result->num_rows === 0) {
            return null;
        }

        $repetitionFactory = new RepetitionFactory();
        $repetition = $repetitionFactory->createFromDbData($result->row);

        return $repetition;
    }

    /**
     * Values are cast to string, as the previous interpolation did, so null
     * still maps to '' and not SQL NULL.
     */
    private function bindings($object)
    {
        return [
            'subscription_id' => (string) $object->getSubscriptionId(),
            'interval' => (string) $object->getInterval(),
            'interval_count' => (string) $object->getIntervalCount(),
            'recurrence_price' => (string) $object->getRecurrencePrice(),
            'cycles' => (string) $object->getCycles(),
        ];
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        return;// TODO: Implement findByPagarmeId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        return;// TODO: Implement listEntities() method.
    }

    public function findBySubscriptionId($subscriptionId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS);
        $subscriptionId = filter_var($subscriptionId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE subscription_id = :subscription_id";

        $result = $this->db->fetch($query, ['subscription_id' => $subscriptionId]);
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