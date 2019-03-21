<?php

namespace Mundipagg\Core\Payment\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Factories\CustomerFactory;


final class CustomerRepository extends AbstractRepository
{
    /** @param Customer $object */
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);

        $obj = json_decode(json_encode($object));

        $query = "
          INSERT INTO $table 
            (
                code, 
                mundipagg_id
            )
          VALUES 
            (
                '{$obj->code}',
                '{$obj->mundipaggId}'
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
        $id = $mundipaggId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);
        $query = "SELECT * FROM $table WHERE mundipagg_id = '$id'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new CustomerFactory();
            $customer = $factory->createFromDbData($result->row);

            return $customer;
        }
        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }
}