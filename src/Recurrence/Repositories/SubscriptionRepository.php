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

class SubscriptionRepository extends AbstractRepository
{
    /**
     * @param AbstractValidString $mundipaggId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        $subscriptionTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
        );
        $id = $mundipaggId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionTable} as recurrence_subscription                  
             WHERE recurrence_subscription.mundipagg_id = '{$id}'             
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionFactory();
        $subscription = $this->attachRelationships(
            $factory->createFromDbData($result->row)
        );

        return $subscription;
    }

    public function findByCode($code)
    {
        $subscriptionTable =
            $this->db->getTable(
                AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
            );

        $query = "
            SELECT *
              FROM {$subscriptionTable} as recurrence_subscription                  
             WHERE recurrence_subscription.code = '{$code}'             
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionFactory();

        $subscription = $this->attachRelationships(
            $factory->createFromDbData($result->row)
        );

        return $subscription;
    }

    /**
     * @param Subscription|AbstractEntity $object
     * @throws Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $subscriptionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION);

        $query = "
          INSERT INTO 
            $subscriptionTable 
            (
                customer_id,
                mundipagg_id, 
                code,                 
                status,
                installments,
                payment_method,
                recurrence_type,
                interval_type,
                interval_count
            )
          VALUES
        ";

        $query .= "
            (
                '{$object->getCustomer()->getMundipaggId()->getValue()}',
                '{$object->getMundipaggId()->getValue()}',
                '{$object->getCode()}',
                '{$object->getStatus()->getStatus()}',
                '{$object->getInstallments()}',
                '{$object->getPaymentMethod()}',
                '{$object->getRecurrenceType()}',
                '{$object->getIntervalType()}',
                '{$object->getIntervalCount()}'
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
        $subscriptionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION);

        $query = "
            UPDATE {$subscriptionTable} SET
              mundipagg_id = '{$object->getMundipaggId()->getValue()}',
              code = '{$object->getCode()}',
              status = '{$object->getStatus()->getStatus()}',
              installments = '{$object->getInstallments()}',
              payment_method = '{$object->getPaymentMethod()}',
              recurrence_type = '{$object->getRecurrenceType()}',
              interval_type = '{$object->getIntervalType()}',
              interval_count = '{$object->getIntervalCount()}'
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
        $table =
            $this->db->getTable(
                AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
            );

        $query = "SELECT * FROM $table WHERE id = '" . $objectId . "'";
        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionFactory();
        $subscription = $this->attachRelationships(
            $factory->createFromDbData($result->row)
        );

        return $subscription;
    }

    /**
     * @param $limit
     * @param $listDisabled
     * @return Subscription[]|array
     * @throws InvalidParamException
     */
    public function listEntities($limit, $listDisabled)
    {
        $table =
            $this->db->getTable(
                AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
            );

        $query = "SELECT * FROM `{$table}` as t";

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query . ";");

        $factory = new SubscriptionFactory();

        $listSubscription = [];
        foreach ($result->rows as $row) {
            $subscription = $this->attachRelationships(
                $factory->createFromDbData($row)
            );

            $listSubscription[] = $subscription;
        }

        return $listSubscription;
    }

    /**
     * @param $customerId
     * @return AbstractEntity|Subscription[]|null
     * @throws InvalidParamException
     */
    public function findByCustomerId($customerId)
    {
        $recurrenceTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION)
        ;

        $customerTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_CUSTOMER)
        ;

        $query = "
            SELECT recurrence_subscription.*
              FROM {$recurrenceTable} as recurrence_subscription
              JOIN {$customerTable} as customer ON (recurrence_subscription.customer_id = customer.mundipagg_id)
             WHERE customer.code = '{$customerId}'
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return [];
        }

        $factory = new SubscriptionFactory();

        $listSubscription = [];
        foreach ($result->rows as $row) {
            $subscription = $this->attachRelationships(
                $factory->createFromDbData($row)
            );
            $listSubscription[] = $subscription;
        }

        return $listSubscription;
    }

    protected function attachRelationships(Subscription $subscription)
    {
        if (!$subscription) {
            return null;
        }

        $chargeFactory = new ChargeRepository();
        $charges = $chargeFactory->findBySubscriptionId($subscription->getMundipaggId());

        foreach ($charges as $charge) {
            $subscription->addCharge($charge);
        }
        return $subscription;
    }
}
