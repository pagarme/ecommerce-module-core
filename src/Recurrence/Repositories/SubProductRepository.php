<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Recurrence\Factories\RepetitionFactory;
use Mundipagg\Core\Recurrence\Factories\SubProductFactory;
use Mundipagg\Core\Recurrence\Interfaces\RecurrenceEntityInterface;

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
                `mundipagg_id`
            ) VALUES (
                '{$object->getProductId()}',
                '{$object->getProductRecurrenceId()}',
                '{$object->getRecurrenceType()}',
                '{$object->getCycles()}',
                '{$object->getQuantity()}',
                '{$object->getMundipaggIdValue()}'
            )
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "
            UPDATE $table SET
                `product_id` = '{$object->getProductId()}',
                `product_recurrence_id` = '{$object->getProductRecurrenceId()}',
                `recurrence_type` = '{$object->getRecurrenceType()}',
                `cycles` = '{$object->getCycles()}',
                `quantity` = '{$object->getQuantity()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

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

    public function findByRecurrence($recurrenceEntity)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "SELECT * FROM $table" .
            " WHERE product_recurrence_id = {$recurrenceEntity->getId()}" .
            " AND recurrence_type = '{$recurrenceEntity->getRecurrenceType()}'";

        $result = $this->db->fetch($query);
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
}