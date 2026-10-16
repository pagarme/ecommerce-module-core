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
use Pagarme\Core\Recurrence\Aggregates\SubscriptionItem;
use Pagarme\Core\Recurrence\Factories\SubProductFactory;
use Pagarme\Core\Recurrence\Factories\SubscriptionFactory;

class SubscriptionRepository extends AbstractRepository
{
    /**
     * @param AbstractValidString $pagarmeId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $subscriptionTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
        );
        $id = $pagarmeId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionTable} as recurrence_subscription                  
             WHERE recurrence_subscription.pagarme_id = :pagarme_id             
        ";

        $result = $this->db->fetch($query, ['pagarme_id' => (string) $id]);
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
        $code = filter_var($code, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = "
            SELECT *
              FROM {$subscriptionTable} as recurrence_subscription                  
             WHERE recurrence_subscription.code = :code             
        ";

        $result = $this->db->fetch($query, ['code' => (string) $code]);
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
                pagarme_id, 
                code,                 
                status,
                installments,
                payment_method,
                recurrence_type,
                interval_type,
                interval_count,
                plan_id
            )
          VALUES
        ";

        $query .= "
            (
                :customer_id,
                :pagarme_id,
                :code,
                :status,
                :installments,
                :payment_method,
                :recurrence_type,
                :interval_type,
                :interval_count,
                :plan_id
            );
        ";

        $this->db->query($query, [
            'customer_id' => (string) $object->getCustomer()->getPagarmeId()->getValue(),
            'pagarme_id' => (string) $object->getPagarmeId()->getValue(),
            'code' => (string) $object->getCode(),
            'status' => (string) $object->getStatus()->getStatus(),
            'installments' => (string) $object->getInstallments(),
            'payment_method' => (string) $object->getPaymentMethod(),
            'recurrence_type' => (string) $object->getRecurrenceType(),
            'interval_type' => (string) $object->getIntervalType(),
            'interval_count' => (string) $object->getIntervalCount(),
            'plan_id' => (string) $object->getPlanIdValue(),
        ]);

        if (!empty($object->getItems())) {
            $this->saveSubscriptionItem($object->getItems());
        }
    }

    protected function saveSubscriptionItem($items)
    {
        foreach ($items as $item) {
            $subscriptionItemsRepository = new SubscriptionItemRepository();
            $subscriptionItemsRepository->save($item);
        }
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
              pagarme_id = :pagarme_id,
              code = :code,
              status = :status,
              installments = :installments,
              payment_method = :payment_method,
              recurrence_type = :recurrence_type,
              interval_type = :interval_type,
              interval_count = :interval_count
            WHERE id = :id
        ";

        $this->db->query($query, [
            'pagarme_id' => (string) $object->getPagarmeId()->getValue(),
            'code' => (string) $object->getCode(),
            'status' => (string) $object->getStatus()->getStatus(),
            'installments' => (string) $object->getInstallments(),
            'payment_method' => (string) $object->getPaymentMethod(),
            'recurrence_type' => (string) $object->getRecurrenceType(),
            'interval_type' => (string) $object->getIntervalType(),
            'interval_count' => (string) $object->getIntervalCount(),
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
        $table =
            $this->db->getTable(
                AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION
            );
        $objectId = filter_var($objectId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE id = :id";
        $result = $this->db->fetch($query, ['id' => (string) $objectId]);

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
        $customerId = filter_var($customerId, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = "
            SELECT recurrence_subscription.*
              FROM {$recurrenceTable} as recurrence_subscription
              JOIN {$customerTable} as customer ON (recurrence_subscription.customer_id = customer.pagarme_id)
             WHERE customer.code = :customer_code
        ";

        $result = $this->db->fetch($query, ['customer_code' => (string) $customerId]);
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
        $charges = $chargeFactory->findBySubscriptionId($subscription->getPagarmeId());
        foreach ($charges as $charge) {
            $subscription->addCharge($charge);
        }

        $subscriptionItemFactory = new SubscriptionItemRepository();
        $subscriptionItems = $subscriptionItemFactory->findBySubscriptionId($subscription->getPagarmeId());

        if ($subscriptionItems === null) {
            return $subscription;
        }

        foreach ($subscriptionItems as $subscriptionItem) {
            $subscription->addItem($subscriptionItem);
        }

        return $subscription;
    }
}
