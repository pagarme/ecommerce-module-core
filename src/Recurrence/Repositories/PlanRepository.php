<?php

namespace Pagarme\Core\Recurrence\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Factories\PlanFactory;

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
                status,
                trial_period_days,
                apply_discount_in_all_product_cycles
            )
          VALUES
            (
                :interval_type,
                :interval_count,
                :name,
                :description,
                :plan_id,
                :product_id,
                :credit_card,
                :installments,
                :boleto,
                :billing_type,
                :status,
                :trial_period_days,
                :apply_discount_in_all_product_cycles
            )
        ";

        $this->db->query($query, $this->bindings($object));
        $object->setId($this->db->getLastId());

        $this->saveSubProducts($object);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );

        $query = "
            UPDATE $table SET
                `interval_type` = :interval_type,
                `interval_count` = :interval_count,
                `name` = :name,
                `description` = :description,
                `plan_id` = :plan_id,
                `product_id` = :product_id,
                `credit_card` = :credit_card,
                `installments` = :installments,
                `boleto` = :boleto,
                `billing_type` = :billing_type,
                `status` = :status,
                `trial_period_days` = :trial_period_days,
                `apply_discount_in_all_product_cycles` = :apply_discount_in_all_product_cycles
            WHERE id = :id
        ";

        $this->db->query($query, $this->bindings($object) + ['id' => $object->getId()]);

        $this->saveSubProducts($object);
    }

    /**
     * Values are cast to string, as the previous interpolation did, so null
     * still maps to '' and not SQL NULL.
     */
    private function bindings($object)
    {
        return [
            'interval_type' => (string) $object->getIntervalType(),
            'interval_count' => (string) $object->getIntervalCount(),
            'name' => (string) $object->getName(),
            'description' => (string) $object->getDescription(),
            'plan_id' => (string) $object->getPagarmeId()->getValue(),
            'product_id' => (string) $object->getProductId(),
            'credit_card' => (string) $object->getCreditCard(),
            'installments' => (string) $object->getAllowInstallments(),
            'boleto' => (string) $object->getBoleto(),
            'billing_type' => (string) $object->getBillingType(),
            'status' => (string) $object->getStatus(),
            'trial_period_days' => (string) $object->getTrialPeriodDays(),
            'apply_discount_in_all_product_cycles' => (string) $object->getApplyDiscountInAllProductCycles(),
        ];
    }

    /** @param Plan $object */
    public function find($objectId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );
        $objectId = filter_var($objectId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE id = :id LIMIT 1";

        $result = $this->db->fetch($query, ['id' => $objectId]);

        if ($result->num_rows === 0) {
            return null;
        }

        return $this->genericFind($result->row);
    }

    public function listEntities($limit, $listDisabled)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );

        $query = "SELECT * FROM {$table}";

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $listPlan = [];
        foreach ($result->rows as $row) {
            $listPlan[] = $this->genericFind($row);
        }

        return $listPlan;
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );

        $query = "DELETE FROM $table WHERE id = :id";

        $result = $this->db->query($query, ['id' => $object->getId()]);

        $this->deleteSubproducts($object);

        return $result;
    }
    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );
        $objectId = $pagarmeId->getValue();

        $query = "SELECT * FROM $table WHERE plan_id = :plan_id LIMIT 1";

        $result = $this->db->fetch($query, ['plan_id' => $objectId]);

        if ($result->num_rows === 0) {
            return null;
        }

        return $this->genericFind($result->row);
    }

    public function deleteSubproducts(AbstractEntity &$object)
    {
        $subProductRepository = new SubProductRepository();
        foreach ($object->getItems() as $subProduct) {
            $subProductRepository->delete($subProduct);
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

    public function findByProductId($productId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN
        );
        $productId = filter_var($productId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE product_id = :product_id LIMIT 1";

        $result = $this->db->fetch($query, ['product_id' => $productId]);

        if ($result->num_rows == 0) {
            return null;
        }
        return $this->genericFind($result->row);
    }

    private function genericFind($row)
    {
        $factory = new PlanFactory();
        $plan = $factory->createFromDbData($row);

        $subProductsRepository = new SubProductRepository();
        $subProducts = $subProductsRepository->findByRecurrence($plan);

        $plan->setItems($subProducts);

        return $plan;
    }
}
