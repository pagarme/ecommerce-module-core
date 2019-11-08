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
                `quantity`
            ) VALUES (
                '{$object->getProductId()}',
                '{$object->getProductRecurrenceId()}',
                '{$object->getRecurrenceType()}',
                '{$object->getCycles()}',
                '{$object->getQuantity()}'
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

    public function findByRecurrence(RecurrenceEntityInterface $recurrenceEntity)
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