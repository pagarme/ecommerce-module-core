<?php

namespace Pagarme\Core\Marketplace\Repositories;

use MundiAPILib\APIException;
use MundiAPILib\Controllers\RecipientsController;
use MundiAPILib\Models\GetTransferSettingsResponse;
use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Marketplace\Factories\RecipientFactory;

class RecipientRepository extends AbstractRepository
{
    protected $controller;

    public function __construct(RecipientsController $controller = null)
    {
        parent::__construct();

        $this->controller = $controller;
    }

    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECIPIENTS
        );

        $query = "
            INSERT INTO $table (
                `external_id`,
                `name`,
                `email`,
                `document_type`,
                `document`,
                `pagarme_id`
            ) VALUES (
                '{$object->getExternalId()}',
                '{$object->getName()}',
                '{$object->getEmail()}',
                '{$object->getDocumentType()}',
                '{$object->getDocument()}',
                '{$object->getPagarmeId()->getValue()}'
            )
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECIPIENTS
        );

        $query = "
            UPDATE $table SET
                `external_id`='{$object->getExternalId()}',
                `name`='{$object->getName()}',
                `email`='{$object->getEmail()}'
            WHERE `id`='{$object->getId()}'
        ";

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECIPIENTS
        );

        $query = "SELECT * FROM $table WHERE id = $objectId";

        $result = $this->db->fetch($query);

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

        $query = "SELECT * FROM {$table} WHERE pagarme_id = {$pagarmeId}";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $recipientFactory = new RecipientFactory();

        return  $recipientFactory->createFromDbData($result->row);
    }

    /**
     * @param Recipient $recipient
     * @return Recipient
     * @throws InvalidParamException
     */
    public function attachBankAccount(Recipient $recipient): Recipient
    {

        try {
            $bankAccount = $this->controller->getRecipient($recipient->getPagarmeId())->defaultBankAccount;
            $recipient->setHolderName($bankAccount->holderName);
            $recipient->setHolderType($bankAccount->holderType);
            $recipient->setHolderDocument($recipient->getDocument());
            $recipient->setBank($bankAccount->bank);
            $recipient->setBranchNumber($bankAccount->branchNumber);
            $recipient->setBranchCheckDigit($bankAccount->branchCheckDigit);
            $recipient->setAccountNumber($bankAccount->accountNumber);
            $recipient->setAccountCheckDigit($bankAccount->accountCheckDigit);
            $recipient->setAccountType($bankAccount->type);
            return $recipient;
        } catch (APIException $e) {
            throw new \Exception(__("Can't get bank default. Please review the information and try again."));
        }
    }

    public function attachTransferSettings(Recipient $recipient): Recipient
    {

        try {
            /** @var GetTransferSettingsResponse $transferSettings */
            $transferSettings = $this->controller->getRecipient($recipient->getPagarmeId())->transferSettings;
            $recipient->setTransferEnabled($transferSettings->transferEnabled);
            $recipient->setTransferDay($transferSettings->transferDay);
            $recipient->setTransferInterval($transferSettings->transferInterval);
            return $recipient;
        } catch (APIException $e) {
            throw new \Exception(__("Can't get transfer settings. Please review the information and try again."));
        }
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

        $query = "SELECT * FROM `$table` as t ";
        $query .= "WHERE t.external_id = '$sellerId';";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return [];
        }

        return $result->row;
    }
}
