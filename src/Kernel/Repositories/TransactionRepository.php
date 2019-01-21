<?php

namespace Mundipagg\Core\Kernel\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\Aggregates\Transaction;
use Mundipagg\Core\Kernel\Factories\ChargeFactory;
use Mundipagg\Core\Kernel\Factories\TransactionFactory;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;

final class TransactionRepository extends AbstractRepository
{
    public function findByChargeId(ChargeId $chargeId)
    {
        $transactionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TRANSACTION);

        $id = $chargeId->getValue();

        $query = "SELECT * FROM `$transactionTable` ";
        $query .= "WHERE charge_id = '{$id}';";

        $result = $this->db->fetch($query);

        $factory = new TransactionFactory();

        return $factory->createFromDbData($result->row);
    }

    /**
     *
     * @param  Transaction $object
     * @throws \Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $transactionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TRANSACTION);

        $simpleObject = json_decode(json_encode($object));

        $query = "
          INSERT INTO 
            $transactionTable 
            (
                mundipagg_id, 
                charge_id,                
                amount, 
                paid_amount, 
                acquirer_nsu,
                acquirer_tid,
                acquirer_auth_code,
                acquirer_name,
                acquirer_message,
                type,
                status,
                created_at
            )
          VALUES 
        ";
        $query .= "
            (
                '{$simpleObject->mundipaggId}',
                '{$simpleObject->chargeId}',                
                {$simpleObject->amount},
                {$simpleObject->paidAmount},
                '{$simpleObject->acquirerNsu}',
                '{$simpleObject->acquirerTid}',
                '{$simpleObject->acquirerAuthCode}',
                '{$simpleObject->acquirerName}',
                '{$simpleObject->acquirerMessage}',
                '{$simpleObject->type}',
                '{$simpleObject->status}',
                '{$simpleObject->createdAt}'
            );
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        //@todo Check if transactions are created or updated on payment events.
        /*$transaction = json_decode(json_encode($object));
        $transactionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TRANSACTION);

        $query = "
            UPDATE $transactionTable SET
              amount = {$transaction->amount},
              paid_amount = {$transaction->paidAmount},                         
              refunded_amount = {$transaction->refundedAmount},                         
              canceled_amount = {$transaction->canceledAmount},
              status = {$transaction->status}
            WHERE id = {$transaction->id}
        ";

        $this->db->query($query);*/
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CHARGE);

        $id = $mundipaggId->getValue();

        $query = "SELECT * FROM `$chargeTable` ";
        $query .= "WHERE mundipagg_id = '{$id}';";

        $result = $this->db->fetch($query);

        $factory = new ChargeFactory();

        return $factory->createFromDbData($result->row);
    }
}