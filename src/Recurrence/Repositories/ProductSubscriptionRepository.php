<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class ProductSubscriptionRepository extends AbstractRepository
{

    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION);

        $query = "
            INSERT INTO $table (
                `product_id`,
                `credit_card`,
                `installments`,
                `boleto`,
                `status`,
                `billing_type`
            ) VALUES (
                '{$object->getProductId()}',
                '{$object->getCreditCard()}',
                '{$object->getAllowInstallments()}',
                '{$object->getBoleto()}',
                '{$object->getStatus()}',
                '{$object->getBillingType()}'
            )
        ";

        $this->db->query($query);

//        $object->setId($this->db->getLastId());
//
//        $this->createRepetitions($object);
//        $this->createSubProducts($object);

    }

    protected function createRepetitions(AbstractEntity &$object)
    {

        $repetitionRepository = new RepetitionRepository();

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
}