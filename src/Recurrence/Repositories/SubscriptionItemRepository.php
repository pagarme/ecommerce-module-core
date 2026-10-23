<?php

namespace Pagarme\Core\Recurrence\Repositories;

use Exception;
use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Recurrence\Aggregates\Subscription;
use Pagarme\Core\Recurrence\Factories\SubscriptionItemFactory;

class SubscriptionItemRepository extends AbstractRepository
{
    /**
     * @param AbstractValidString $pagarmeId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );
        $id = $pagarmeId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}                  
             WHERE pagarme_id = :pagarme_id             
        ";

        $result = $this->db->fetch($query, ['pagarme_id' => (string) $id]);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();
        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    public function findBySubscriptionId(AbstractValidString $pagarmeId)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );
        $id = $pagarmeId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}
             WHERE subscription_id = :subscription_id
        ";

        $result = $this->db->fetch($query, ['subscription_id' => (string) $id]);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();

        $listSubscriptionItem = [];
        foreach ($result->rows as $row) {
            $subscriptionItem = $factory->createFromDbData($row);
            $listSubscriptionItem[] = $subscriptionItem;
        }

        return $listSubscriptionItem;
    }

    public function findByCode($code)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );
        $code = filter_var($code, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = "
            SELECT *
              FROM {$subscriptionItemTable}                  
             WHERE code = :code             
        ";

        $result = $this->db->fetch($query, ['code' => (string) $code]);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();

        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    /**
     * @param Subscription|AbstractEntity $object
     * @throws Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "
          INSERT INTO 
            $subscriptionItemTable 
            (
                pagarme_id, 
                subscription_id,
                code,                
                quantity
            )
          VALUES
        ";

        $query .= "
            (
                :pagarme_id,
                :subscription_id,
                :code,
                :quantity
            );
        ";

        $this->db->query($query, [
            'pagarme_id' => (string) $object->getPagarmeId()->getValue(),
            'subscription_id' => (string) $object->getSubscriptionId()->getValue(),
            'code' => (string) $object->getCode(),
            'quantity' => (string) $object->getQuantity(),
        ]);
    }

    /**
     * @param Subscription|AbstractEntity $object
     * @throws Exception
     */
    protected function update(AbstractEntity &$object)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "
            UPDATE {$subscriptionItemTable} SET
              pagarme_id = :pagarme_id,
              code = :code,
              subscription_id = :subscription_id,
              quantity = :quantity
            WHERE id = :id
        ";

        $this->db->query($query, [
            'pagarme_id' => (string) $object->getPagarmeId()->getValue(),
            'code' => (string) $object->getCode(),
            'subscription_id' => (string) $object->getSubscriptionId()->getValue(),
            'quantity' => (string) $object->getQuantity(),
            'id' => $object->getId(),
        ]);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param $objectId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function find($objectId)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );
        $objectId = filter_var($objectId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM {$subscriptionItemTable} WHERE id = :id";
        $result = $this->db->fetch($query, ['id' => (string) $objectId]);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();

        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    /**
     * @param $limit
     * @param $listDisabled
     * @return Subscription[]|array
     * @throws InvalidParamException
     */
    public function listEntities($limit, $listDisabled)
    {
        //@TODO Implement listEntities method
    }
}
