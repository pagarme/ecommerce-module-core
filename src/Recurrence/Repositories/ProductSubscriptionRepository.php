<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Recurrence\Factories\ProductSubscriptionFactory;

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
                `billing_type`
            ) VALUES (
                '{$object->getProductId()}',
                '{$object->getCreditCard()}',
                '{$object->getAllowInstallments()}',
                '{$object->getBoleto()}',
                '{$object->getBillingType()}'
            )
        ";

        $this->db->query($query);

        $object->setId($this->db->getLastId());

        $this->saveRepetitions($object);
        $this->saveSubProducts($object);
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
                `billing_type` = '{$object->getBillingType()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);

        $this->saveRepetitions($object);
        $this->saveSubProducts($object);
    }

    public function saveRepetitions(AbstractEntity &$object)
    {
        $repetitionRepository = new RepetitionRepository();
        foreach ($object->getRepetitions() as $repetition) {
            $repetition->setSubscriptionId($object->getId());
            $repetitionRepository->save($repetition);
        }
    }

    public function saveSubProducts(AbstractEntity &$object)
    {
        $subProductRepository = new SubProductRepository();
        foreach ($object->getItems() as $subProduct) {
            $subProduct->setProductRecurrenceId($object->getId());
            $subProduct->setRecurrenceType($object->getRecurrenceType());
            $subProductRepository->save($subProduct);
        }
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION);

        $query = "DELETE FROM $table WHERE id = {$object->getId()}";

        $result = $this->db->query($query);

        $this->deleteRepetitions($object);
        $this->deleteSubProducts($object);

        return $result;
    }

    public function deleteRepetitions(AbstractEntity &$object)
    {
        $repetitionRepository = new RepetitionRepository();
        foreach ($object->getRepetitions() as $repetition) {
            $repetitionRepository->delete($repetition);
        }
    }

    public function deleteSubProducts(AbstractEntity &$object)
    {
        $subProductRepository = new SubProductRepository();
        foreach ($object->getItems() as $subProduct) {
            $subProductRepository->delete($subProduct);
        }
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION);

        $query = "SELECT * FROM $table WHERE id = $objectId";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $productSubscriptionFactory = new ProductSubscriptionFactory();
        $productSubscription = $productSubscriptionFactory->createFromDbData($result->row);

        $repetitionRepository = new RepetitionRepository();
        $repetitions = $repetitionRepository->findBySubscriptionId($objectId);

        $subProductsRepository = new SubProductRepository();
        $subProducts = $subProductsRepository->findByRecurrence($productSubscription);

        foreach ($repetitions as $repetition) {
            $productSubscription->addRepetition($repetition);
        }

        foreach ($subProducts as $subProduct) {
            $productSubscription->addItems($subProduct);
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