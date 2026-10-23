<?php

namespace Pagarme\Core\Marketplace\Repositories;

use PagarmeCoreApiLib\APIException;
use PagarmeCoreApiLib\Models\GetBankAccountResponse;
use PagarmeCoreApiLib\Models\GetTransferSettingsResponse;
use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Marketplace\Factories\RecipientFactory;

class RecipientRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECIPIENTS
        );

        $query = "INSERT INTO $table (
            `external_id`,
            `name`,
            `email`,
            `type`,
            `document`,
            `pagarme_id`";

        $bindings = [
            'external_id' => (string) $object->getExternalId(),
            'name' => (string) $object->getName(),
            'email' => (string) $object->getEmail(),
            'type' => (string) $object->getDocumentType(),
            'document' => (string) $object->getDocument(),
            'pagarme_id' => (string) $object->getPagarmeId()->getValue(),
        ];

        $queryStatusFieldValue = '';

        if (!empty($object->getStatus())) {
            $query .= ",`status`";
            $queryStatusFieldValue = ",:status";
            $bindings['status'] = $object->getStatus();
        }

        $query .= ") VALUES (
            :external_id,
            :name,
            :email,
            :type,
            :document,
            :pagarme_id";

        $query .= $queryStatusFieldValue;

        $query .= ")";

        $this->db->query($query, $bindings);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECIPIENTS
        );

        $query = "
            UPDATE $table SET
                `external_id` = :external_id,
                `name` = :name,
                `email` = :email,
                `pagarme_id` = :pagarme_id,
                `status` = :status
            WHERE `id` = :id
        ";

        $this->db->query($query, [
            'external_id' => (string) $object->getExternalId(),
            'name' => (string) $object->getName(),
            'email' => (string) $object->getEmail(),
            'pagarme_id' => (string) $object->getPagarmeId()->getValue(),
            'status' => (string) $object->getStatus(),
            'id' => (string) $object->getId(),
        ]);
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECIPIENTS
        );

        $query = "DELETE FROM $table WHERE id = :id";

        return $this->db->query($query, ['id' => $object->getId()]);
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECIPIENTS
        );
        $objectId = filter_var($objectId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE id = :id";

        $result = $this->db->fetch($query, ['id' => $objectId]);

        if ($result->num_rows === 0) {
            return null;
        }

        $recipientFactory = new RecipientFactory();

        return  $recipientFactory->createFromDbData($result->row);
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECIPIENTS
        );

        $query = "SELECT * FROM {$table} WHERE pagarme_id = :pagarme_id";

        $result = $this->db->fetch($query, ['pagarme_id' => (string) $pagarmeId]);

        if ($result->num_rows === 0) {
            return null;
        }

        $recipientFactory = new RecipientFactory();

        return  $recipientFactory->createFromDbData($result->row);
    }

    /**
     * @param Recipient $recipient
     * @param GetBankAccountResponse $bankAccount
     * @return Recipient
     * @throws InvalidParamException
     */
    public function attachBankAccount(Recipient $recipient, GetBankAccountResponse $bankAccount): Recipient
    {
        try {
            $recipient->setHolderName($bankAccount->holderName);
            $recipient->setHolderType($bankAccount->holderType);
            $recipient->setHolderDocument($recipient->getDocument());
            $recipient->setBank($bankAccount->bank);
            $recipient->setBranchNumber($bankAccount->branchNumber);
            $recipient->setBranchCheckDigit($bankAccount->branchCheckDigit);
            $recipient->setAccountNumber($bankAccount->accountNumber);
            $recipient->setAccountCheckDigit($bankAccount->accountCheckDigit);
            $recipient->setAccountType($bankAccount->type);
        } catch (InvalidParamException $e) {
        }

        return $recipient;
    }

    public function attachTransferSettings(Recipient $recipient, GetTransferSettingsResponse $transferSettings): Recipient
    {
        $recipient->setTransferEnabled($transferSettings->transferEnabled);
        $recipient->setTransferDay($transferSettings->transferDay);
        $recipient->setTransferInterval($transferSettings->transferInterval);
        return $recipient;
    }

    public function attachDocumentFromDb(Recipient $recipient)
    {
        $recipientFromDb = $this->find($recipient->getId());
        $recipient->setDocument($recipientFromDb->getDocument());

        return $recipient;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findBySellerId($sellerId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECIPIENTS
        );
        $sellerId = filter_var($sellerId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM `$table` as t ";
        $query .= "WHERE t.external_id = :external_id;";

        $result = $this->db->fetch($query, ['external_id' => (string) $sellerId]);

        if ($result->num_rows === 0) {
            return [];
        }

        return $result->row;
    }
}
