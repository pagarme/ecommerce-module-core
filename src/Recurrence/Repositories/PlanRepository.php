<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Factories\PlanFactory;

final class PlanRepository extends AbstractRepository
{
    /** @param Plan $object */
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );

        $query = "
          INSERT INTO $table 
            (
                interval_type, 
                interval_count,
                name,
                description,
                plan_id,
                product_id,
                credit_card,
                installments,
                boleto,
                billing_type,
                status
            )
          VALUES 
            (
                '{$object->getIntervalType()}',
                '{$object->getIntervalCount()}',
                '{$object->getName()}',
                '{$object->getDescription()}',
                '{$object->getMundipaggId()->getValue()}',
                '{$object->getProductId()}',
                '{$object->getCreditCard()}',
                '{$object->getAllowInstallments()}',
                '{$object->getBoleto()}',
                '{$object->getBillingType()}',
                '{$object->getStatus()}'
            )          
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        // TODO: Implement update() method.
    }

    /** @param Plan $object */
    public function find($objectId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );
        $query = "SELECT * FROM $table WHERE id = '$id' LIMIT 1";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new PlanFactory();
            $plan = $factory->createFromDbData($result->row);

            return $plan;
        }
        return null;
    }

    /*public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        $id = $mundipaggId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);
        $query = "SELECT * FROM $table WHERE mundipagg_id = '$id'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new CustomerFactory();
            $customer = $factory->createFromDbData(end($result->rows));

            return $customer;
        }
        return null;
    }*/

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function delete(AbstractEntity $object)
    {

    }
    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {

    }
}