<?php

namespace Pagarme\Core\Kernel\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\Factories\ChargeFactory;
use Pagarme\Core\Kernel\Factories\TransactionFactory;
use Pagarme\Core\Kernel\Helper\StringFunctionsHelper;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;

final class TransactionRepository extends AbstractRepository
{
    public function findByChargeId(ChargeId $chargeId)
    {
        $transactionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TRANSACTION);

        $id = $chargeId->getValue();

        $query = "SELECT * FROM `$transactionTable` ";
        $query .= "WHERE charge_id = '{$id}';";

        $result = $this->db->fetch($query);
        $helper = new StringFunctionsHelper();

        if (!empty($result['card_data'])) {
            $result['card_data'] = json_encode(
                $helper->removeLineBreaksRecursive(
                    json_decode($result['card_data'], true)
                )
            );
        }

        if (!empty($result['transaction_data'])) {
            $result['transaction_data'] = json_encode(
                $helper->removeLineBreaksRecursive(
                    json_decode($result['transaction_data'], true)
                )
            );
        }

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
        $helper = new StringFunctionsHelper();

        // Sanitize all string fields interpolated into the SQL string so they
        // cannot break the query via unescaped single quotes. boletoUrl is
        // exempted from special-char stripping so its query params (&, ?, =)
        // survive; it still gets quote-neutralized for SQL safety.
        $boletoUrl = $simpleObject->boletoUrl;
        $simpleObject = $helper->cleanRecursive($simpleObject);
        $simpleObject->boletoUrl = $helper->cleanUrlToDb($boletoUrl);

        $cardData = json_encode(
            $helper->removeLineBreaksRecursive($simpleObject->cardData)
        );

        $transactionData = json_encode(
            $helper->cleanRecursive($object->getPostData())
        );

        $query = "
          INSERT INTO 
            $transactionTable 
            (
                pagarme_id, 
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
                created_at,
                boleto_url,
                card_data,
                transaction_data
            )
          VALUES 
        ";
        $query .= "
            (
                '{$simpleObject->pagarmeId}',
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
                '{$simpleObject->createdAt}',
                '{$simpleObject->boletoUrl}',
                '{$cardData}',
                '{$transactionData}'
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

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CHARGE);

        $id = $pagarmeId->getValue();

        $query = "SELECT * FROM `$chargeTable` ";
        $query .= "WHERE pagarme_id = '{$id}';";

        $result = $this->db->fetch($query);

        $factory = new ChargeFactory();

        return $factory->createFromDbData($result->row);
    }
}