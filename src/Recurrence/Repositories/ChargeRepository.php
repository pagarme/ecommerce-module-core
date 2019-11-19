<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Factories\ChargeFactory;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use Mundipagg\Core\Kernel\Repositories\TransactionRepository;

final class ChargeRepository extends AbstractRepository
{
    public function findByOrderId(OrderId $orderId)
    {
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_CHARGE);
        $transactionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TRANSACTION);

        $id = $orderId->getValue();

        $query = "
            SELECT 
                c.*, 
                GROUP_CONCAT(c.id) as id, 
                GROUP_CONCAT(t.id) as tran_id, 
                GROUP_CONCAT(t.mundipagg_id) as tran_mundipagg_id,
                GROUP_CONCAT(t.charge_id) as tran_charge_id,
                GROUP_CONCAT(t.amount) as tran_amount,
                GROUP_CONCAT(t.paid_amount) as tran_paid_amount,
                GROUP_CONCAT(t.acquirer_name) as tran_acquirer_name,                
                GROUP_CONCAT(t.acquirer_message) as tran_acquirer_message,                
                GROUP_CONCAT(t.acquirer_nsu) as tran_acquirer_nsu,                
                GROUP_CONCAT(t.acquirer_tid) as tran_acquirer_tid,                
                GROUP_CONCAT(t.acquirer_auth_code) as tran_acquirer_auth_code,                
                GROUP_CONCAT(t.type) as tran_type,
                GROUP_CONCAT(t.status) as tran_status,
                GROUP_CONCAT(t.created_at) as tran_created_at,
                GROUP_CONCAT(t.boleto_url) as tran_boleto_url,
                GROUP_CONCAT(t.card_data SEPARATOR '---') as tran_card_data
            FROM
                $chargeTable as c 
                LEFT JOIN $transactionTable as t  
                  ON c.mundipagg_id = t.charge_id 
            WHERE c.order_id = '$id'
            GROUP BY c.id;
        ";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return [];
        }

        $factory = new ChargeFactory();

        $charges = [];
        foreach ($result->rows as $row) {
            $charges[] = $factory->createFromDbData($row);
        }

        return $charges;
    }

    /**
     *
     * @param  Charge $object
     * @throws \Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_CHARGE);

        $query = "
          INSERT INTO 
            $chargeTable 
            (
                mundipagg_id, 
                invoice_id,
                subscription_id, 
                code, 
                amount, 
                paid_amount,
                canceled_amount,
                refunded_amount,
                status,
                metadata,
                payment_method,
                boleto_link,
                cycle_start,
                cycle_end
            )
          VALUES 
        ";

        $metadata = \json_encode($object->getMetadata());

        $query .= "
            (
                '{$object->getMundipaggId()->getValue()}',
                '{$object->getInvoice()->getMundipaggId()->getValue()}',
                '{$object->getInvoice()->getSubscriptionId()->getValue()}',
                '{$object->getCode()}',
                {$object->getAmount()},
                {$object->getPaidAmount()},
                {$object->getCanceledAmount()},
                {$object->getRefundedAmount()},
                '{$object->getStatus()->getStatus()}',
                '{$metadata}', 
                '{$object->getPaymentMethod()->getPaymentMethod()}',
                '{$object->getLastTransaction()->getBoletoUrl()}',
               '{$object->getCycleStart()->format('Y-m-d H:i:s')}',
               '{$object->getCycleEnd()->format('Y-m-d H:i:s')}' 
            );
        ";

        $this->db->query($query);

        $transactionRepository = new TransactionRepository();
        foreach ($object->getTransactions() as $transaction) {
            $transactionRepository->save($transaction);
            $object->updateTransaction($transaction, true);
        }
    }

    protected function update(AbstractEntity &$object)
    {
        $charge = json_decode(json_encode($object));
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_CHARGE);

        $metadata = json_encode($charge->metadata);

        $query = "
            UPDATE $chargeTable SET
              amount = {$charge->amount},
              paid_amount = {$charge->paidAmount},                         
              refunded_amount = {$charge->refundedAmount},                         
              canceled_amount = {$charge->canceledAmount},
              status = '{$charge->status}',
              metadata = '{$metadata}',
              customer_id = '{$charge->customerId}'
            WHERE id = {$charge->id}
        ";

        $this->db->query($query);

        //update Transactions;
        $transactionRepository = new TransactionRepository();
        foreach ($object->getTransactions() as $transaction) {
            $transactionRepository->save($transaction);
            $object->updateTransaction($transaction, true);
        }
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

        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_CHARGE);
        $transactionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TRANSACTION);

        $id = $mundipaggId->getValue();

        $query = "
            SELECT 
                c.*, 
                GROUP_CONCAT(t.id) as tran_id, 
                GROUP_CONCAT(t.mundipagg_id) as tran_mundipagg_id,
                GROUP_CONCAT(t.charge_id) as tran_charge_id,
                GROUP_CONCAT(t.amount) as tran_amount,
                GROUP_CONCAT(t.paid_amount) as tran_paid_amount,
                GROUP_CONCAT(t.acquirer_name) as tran_acquirer_name,                
                GROUP_CONCAT(t.acquirer_message) as tran_acquirer_message,                
                GROUP_CONCAT(t.acquirer_nsu) as tran_acquirer_nsu,                
                GROUP_CONCAT(t.acquirer_tid) as tran_acquirer_tid,                
                GROUP_CONCAT(t.acquirer_auth_code) as tran_acquirer_auth_code,                
                GROUP_CONCAT(t.type) as tran_type,
                GROUP_CONCAT(t.status) as tran_status,
                GROUP_CONCAT(t.created_at) as tran_created_at,
                GROUP_CONCAT(t.boleto_url) as tran_boleto_url,
                GROUP_CONCAT(t.card_data SEPARATOR '---') as tran_card_data
            FROM
                $chargeTable as c 
                LEFT JOIN $transactionTable as t  
                  ON c.mundipagg_id = t.charge_id 
            WHERE c.mundipagg_id = '$id'
            GROUP BY c.id;
        ";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new \Mundipagg\Core\Recurrence\Factories\ChargeFactory();

        return $factory->createFromDbData($result->row);
    }
}