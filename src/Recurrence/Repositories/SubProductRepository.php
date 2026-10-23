<?php

namespace Pagarme\Core\Recurrence\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Factories\RepetitionFactory;
use Pagarme\Core\Recurrence\Factories\SubProductFactory;
use Pagarme\Core\Recurrence\Interfaces\RecurrenceEntityInterface;

class SubProductRepository extends AbstractRepository
{

    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "
            INSERT INTO $table (
                `product_id`,
                `product_recurrence_id`,
                `recurrence_type`,
                `cycles`,
                `quantity`,
                `pagarme_id`
            ) VALUES (
                :product_id,
                :product_recurrence_id,
                :recurrence_type,
                :cycles,
                :quantity,
                :pagarme_id
            )
        ";

        $this->db->query($query, [
            'product_id' => (string) $object->getProductId(),
            'product_recurrence_id' => (string) $object->getProductRecurrenceId(),
            'recurrence_type' => (string) $object->getRecurrenceType(),
            'cycles' => (string) $object->getCycles(),
            'quantity' => (string) $object->getQuantity(),
            'pagarme_id' => (string) $object->getPagarmeIdValue(),
        ]);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "
            UPDATE $table SET
                `product_id` = :product_id,
                `product_recurrence_id` = :product_recurrence_id,
                `recurrence_type` = :recurrence_type,
                `cycles` = :cycles,
                `quantity` = :quantity
            WHERE id = :id
        ";

        $this->db->query($query, [
            'product_id' => (string) $object->getProductId(),
            'product_recurrence_id' => (string) $object->getProductRecurrenceId(),
            'recurrence_type' => (string) $object->getRecurrenceType(),
            'cycles' => (string) $object->getCycles(),
            'quantity' => (string) $object->getQuantity(),
            'id' => $object->getId(),
        ]);
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "DELETE FROM $table WHERE id = :id";

        $this->db->query($query, ['id' => $object->getId()]);
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        // TODO: Implement findByPagarmeId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByRecurrence($recurrenceEntity)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "SELECT * FROM $table" .
            " WHERE product_recurrence_id = :product_recurrence_id" .
            " AND recurrence_type = :recurrence_type";

        $result = $this->db->fetch($query, [
            'product_recurrence_id' => $recurrenceEntity->getId(),
            'recurrence_type' => (string) $recurrenceEntity->getRecurrenceType(),
        ]);
        $subProducts = [];

        if ($result->num_rows === 0) {
            return $subProducts;
        }

        foreach ($result->rows as $row) {
            $subProductFactory = new SubProductFactory();
            $subProducts[] = $subProductFactory->createFromDbData($row);
        }

        return $subProducts;
    }

    public function findByRecurrenceIdAndProductId($recurrenceId, $productId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);
        $recurrenceId = filter_var($recurrenceId, FILTER_SANITIZE_NUMBER_INT);
        $productId = filter_var($productId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table" .
            " WHERE product_recurrence_id = :recurrence_id" .
            " AND product_id = :product_id";

        $result = $this->db->fetch($query, [
            'recurrence_id' => $recurrenceId,
            'product_id' => $productId,
        ]);

        if ($result->num_rows === 0) {
            return null;
        }

        $subProductFactory = new SubProductFactory();
        return $subProductFactory->createFromDbData($result->row);
    }
}