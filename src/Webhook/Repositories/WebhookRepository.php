<?php

namespace Mundipagg\Core\Webhook\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Webhook\Factories\WebhookFactory;

class WebhookRepository extends AbstractRepository
{
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_WEBHOOK);
        $query = "INSERT INTO $table (mundipagg_id) VALUES ('{$object->getMundipaggId()->getValue()}')";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {

    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_WEBHOOK);
        $query = "SELECT * FROM $table WHERE id = '$objectId'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new WebhookFactory();
            $webhook = $factory->createFromDbData($result->row);

            return $webhook;
        }
        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByMundipaggId($mundipaggId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_WEBHOOK);
        $query = "SELECT * FROM $table WHERE mundipagg_id = '$mundipaggId'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new WebhookFactory();
            $webhook = $factory->createFromDbData($result->row);

            return $webhook;
        }
        return null;
    }
}