<?php

namespace Pagarme\Core\Marketplace\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class RecipientRepository extends AbstractRepository
{
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
        // TODO: Implement update() method.
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        // TODO: Implement findByPagarmeId() method.
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