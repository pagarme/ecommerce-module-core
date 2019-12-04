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
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );

        $query = "
            INSERT INTO $table (
                `product_id`,
                `credit_card`,
                `allow_installments`,
                `boleto`,
                `sell_as_normal_product`,
                `cycles`,
                `billing_type`
            ) VALUES (
                '{$object->getProductId()}',
                '{$object->getCreditCard()}',
                '{$object->getAllowInstallments()}',
                '{$object->getBoleto()}',
                '{$object->getSellAsNormalProduct()}',
                 {$object->getCycles()},
                '{$object->getBillingType()}'
            )
        ";

        $this->db->query($query);

        $object->setId($this->db->getLastId());

        $this->saveRepetitions($object);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );

        $query = "
            UPDATE $table SET 
                `product_id` = '{$object->getProductId()}',
                `credit_card` = '{$object->getCreditCard()}',
                `allow_installments` = '{$object->getAllowInstallments()}',
                `boleto` = '{$object->getBoleto()}',
                `sell_as_normal_product` = '{$object->getSellAsNormalProduct()}',
                `cycles` = '{$object->getCycles()}',
                `billing_type` = '{$object->getBillingType()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);

        $this->saveRepetitions($object);
    }

    public function saveRepetitions(AbstractEntity &$object)
    {
        $repetitionRepository = new RepetitionRepository();
        foreach ($object->getRepetitions() as &$repetition) {
            $repetition->setSubscriptionId($object->getId());
            $repetition = $repetitionRepository->save($repetition);
        }
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );

        $query = "DELETE FROM $table WHERE id = {$object->getId()}";

        $result = $this->db->query($query);

        $this->deleteRepetitions($object);

        return $result;
    }

    public function deleteRepetitions(AbstractEntity &$object)
    {
        $repetitionRepository = new RepetitionRepository();
        foreach ($object->getRepetitions() as $repetition) {
            $repetitionRepository->delete($repetition);
        }
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );

        $query = "SELECT * FROM $table WHERE id = $objectId";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $productSubscriptionFactory = new ProductSubscriptionFactory();
        $productSubscription =
            $productSubscriptionFactory->createFromDbData($result->row);

        $repetitionRepository = new RepetitionRepository();
        $repetitions = $repetitionRepository->findBySubscriptionId($objectId);

        foreach ($repetitions as $repetition) {
            $productSubscription->addRepetition($repetition);
        }

        return $productSubscription;
    }

    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        return; // TODO: Implement findByMundipaggId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        $table = $this->db->getTable(
                AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
            );

        $query = "SELECT * FROM `$table` as t";

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query . ";");

        $productSubscriptions = [];
        foreach ($result->rows as $row) {

            $factory = new ProductSubscriptionFactory();
            $productSubscription = $factory->createFromDBData($row);

            $repetitionRepository = new RepetitionRepository();
            $repetitions = $repetitionRepository->findBySubscriptionId(
                $productSubscription->getId()
            );

            $productSubscription->setRepetitions($repetitions);

            $productSubscriptions[] = $productSubscription;
        }

        return $productSubscriptions;
    }

    public function findByProductId($productId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );

        $query = "SELECT * FROM $table WHERE product_id = $productId";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $productSubscriptionFactory = new ProductSubscriptionFactory();
        $productSubscription =
            $productSubscriptionFactory->createFromDbData($result->row);

        $repetitionRepository = new RepetitionRepository();
        $repetitions = $repetitionRepository->findBySubscriptionId(
            $productSubscription->getId()
        );

        foreach ($repetitions as $repetition) {
            $productSubscription->addRepetition($repetition);
        }

        return $productSubscription;
    }
}