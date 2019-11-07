<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Recurrence\Factories\ProductSubscriptionFactory;
use Mundipagg\Core\Recurrence\Factories\RepetitionFactory;

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

        $object->setId($this->db->getLastId());

        $this->saveRepetitions($object);

//        $this->createSubProducts($object);

    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION);

        $query = "
            UPDATE $table SET 
                `product_id` = '{$object->getProductId()}',
                `credit_card` = '{$object->getCreditCard()}',
                `installments` = '{$object->getAllowInstallments()}',
                `boleto` = '{$object->getBoleto()}',
                `status` = '{$object->getStatus()}',
                `billing_type` = '{$object->getBillingType()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);

        $object->setId($this->db->getLastId());
        $this->saveRepetitions($object);

    }

    public function saveRepetitions(AbstractEntity &$object)
    {
        $repetitionRepository = new RepetitionRepository();
        foreach ($object->getRepetitions() as $repetition) {
            $repetition->setSubscriptionId($object->getId());
            $repetitionRepository->save($repetition);
        }
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION);

        $query = "SELECT * FROM $table WHERE id = $objectId";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $repetitionRepository = new RepetitionRepository();
        $repetitions = $repetitionRepository->findBySubscriptionId($objectId);

        $productSubscriptionFactory = new ProductSubscriptionFactory();
        $productSubscription = $productSubscriptionFactory->createFromDbData($result->row);

        foreach ($repetitions as $repetition) {
            $productSubscription->addRepetition($repetition);
        }

        return $productSubscription;
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