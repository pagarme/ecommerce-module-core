<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Exception;
use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\Factories\SubscriptionFactory;

class SubscriptionItemRepository extends AbstractRepository
{
    /**
     * @param AbstractValidString $mundipaggId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );
        $id = $mundipaggId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}                  
             WHERE mundipagg_id = '{$id}'             
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();
        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    public function findByCode($code)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}                  
             WHERE code = '{$code}'             
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionFactory();

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
                mundipagg_id, 
                subscription_id,
                code,                
                quantity
            )
          VALUES
        ";

        $query .= "
            (
                '{$object->getMundipaggId()->getValue()}',
                '{$object->getSubscriptionId()->getValue()}',
                '{$object->getCode()}',
                '{$object->getQuantity()}'
            );
        ";

        $this->db->query($query);
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
              mundipagg_id = '{$object->getMundipaggId()->getValue()}',
              code = '{$object->getCode()}',
              subscription_id = '{$object->getSubscriptionId()->getValue()}',
              quantity = '{$object->getQuantity()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);
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

        $query = "SELECT * FROM {$subscriptionItemTable} WHERE id = '" . $objectId . "'";
        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionFactory();

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
