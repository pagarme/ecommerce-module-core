<?php

namespace Mundipagg\Core\Payment\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\Aggregates\SavedCard;
use Mundipagg\Core\Payment\Factories\SavedCardFactory;

final class SavedCardRepository extends AbstractRepository
{
    /**
     * @param CustomerId $customerId
     * @return Savedcard[]
     * @throws \Exception
     */
    public function findByOwnerId(CustomerId $customerId)
    {
        $id = $customerId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);
        $query = "SELECT * FROM $table WHERE owner_id = '$id'";

        $result = $this->db->fetch($query);

        $factory = new SavedCardFactory();
        $savedCards = [];
        foreach ($result->rows as $row) {
            $savedCards[] = $factory->createFromDbData($row);
        }
        return $savedCards;
    }

    /** @param SavedCard $object */
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);

        $obj = json_decode(json_encode($object));

        if ($object->getOwnerId() === null) {
            throw new InvalidParamException('
            You can\'t save a card without an onwer!' , null
            );
        }

        $query = "
          INSERT INTO $table 
            (
                mundipagg_id, 
                owner_id,
                owner_name,
                first_six_digits, 
                last_four_digits,
                brand,
                created_at
            )
          VALUES 
            (
                '{$obj->mundipaggId}',
                '{$obj->ownerId}',
                '{$obj->ownerName}',
                '{$obj->firstSixDigits}',
                '{$obj->lastFourDigits}',
                '{$obj->brand}',
                '{$obj->createdAt}'
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
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);
        $query = "DELETE FROM $table where id = {$object->getId()}";

        $this->db->query($query);
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);
        $query = "SELECT * FROM $table WHERE id = '$objectId'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new SavedCardFactory();
            $savedCard = $factory->createFromDbData($result->row);

            return $savedCard;
        }
        return null;
    }

    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        $id = $mundipaggId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);
        $query = "SELECT * FROM $table WHERE mundipagg_id = '$id'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new SavedCardFactory();
            $savedCard = $factory->createFromDbData($result->row);

            return $savedCard;
        }
        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }
}