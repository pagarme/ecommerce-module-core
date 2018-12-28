<?php

namespace Mundipagg\Core\Kernel\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

final class OrderRepository extends AbstractRepository
{
    protected function create(AbstractEntity &$object)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $order = json_decode(json_encode($object));

        $query = "
          INSERT INTO $orderTable (`mundipagg_id`, `code`, `status`) 
          VALUES ('{$order->mundipaggId}', {$order->code}, '{$order->status}');
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
        $id = $mundipaggId->getValue();
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE mundipagg_id = '{$id}';";

        $result = $this->db->fetch($query);

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }
}