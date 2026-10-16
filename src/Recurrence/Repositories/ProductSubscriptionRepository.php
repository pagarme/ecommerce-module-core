<?php

namespace Pagarme\Core\Recurrence\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Factories\ProductSubscriptionFactory;

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
                `billing_type`,
                `apply_discount_in_all_product_cycles`
            ) VALUES (
                :product_id,
                :credit_card,
                :allow_installments,
                :boleto,
                :sell_as_normal_product,
                :billing_type,
                :apply_discount_in_all_product_cycles
            )
        ";

        $this->db->query($query, $this->bindings($object));

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
                `product_id` = :product_id,
                `credit_card` = :credit_card,
                `allow_installments` = :allow_installments,
                `boleto` = :boleto,
                `sell_as_normal_product` = :sell_as_normal_product,
                `billing_type` = :billing_type,
                `apply_discount_in_all_product_cycles` = :apply_discount_in_all_product_cycles
            WHERE id = :id
        ";

        $this->db->query($query, $this->bindings($object) + ['id' => $object->getId()]);

        $this->saveRepetitions($object);
    }

    /**
     * Values are cast to string, as the previous interpolation did, so null
     * still maps to '' and not SQL NULL.
     */
    private function bindings($object)
    {
        return [
            'product_id' => (string) $object->getProductId(),
            'credit_card' => (string) $object->getCreditCard(),
            'allow_installments' => (string) $object->getAllowInstallments(),
            'boleto' => (string) $object->getBoleto(),
            'sell_as_normal_product' => (string) $object->getSellAsNormalProduct(),
            'billing_type' => (string) $object->getBillingType(),
            'apply_discount_in_all_product_cycles' => (string) $object->getApplyDiscountInAllProductCycles(),
        ];
    }

    public function saveRepetitions(AbstractEntity &$object)
    {
        $repetitionRepository = new RepetitionRepository();
        foreach ($object->getRepetitions() as &$repetition) {
            $repetition->setId(null);
            $repetition->setSubscriptionId($object->getId());
            $repetition = $repetitionRepository->save($repetition);
        }
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );

        $query = "DELETE FROM $table WHERE id = :id";

        $result = $this->db->query($query, ['id' => $object->getId()]);

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
        $objectId = filter_var($objectId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE id = :id";

        $result = $this->db->fetch($query, ['id' => $objectId]);

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

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        // TODO: Implement findByPagarmeId() method.
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
        $productId = filter_var($productId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE product_id = :product_id";

        $result = $this->db->fetch($query, ['product_id' => $productId]);
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
