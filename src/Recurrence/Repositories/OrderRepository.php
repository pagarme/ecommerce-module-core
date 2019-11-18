<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\Factories\OrderFactory;

class OrderRepository extends AbstractRepository
{
    public function findByCode($codeId)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE code = '{$codeId}';";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

}