<?php

namespace Mundipagg\Core\Kernel\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\Factories\ConfigurationFactory;

class ConfigurationRepository extends AbstractRepository
{
    protected function create(AbstractEntity &$object)
    {
        $jsonEncoded = json_encode($object);

        $query = "
             INSERT INTO `" . $this->db->getTable('CONFIGURATION_TABLE') . "` (data)" .
            "VALUES ('$jsonEncoded')
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $query = "
            SELECT * FROM `" . $this->db->getTable('CONFIGURATION_TABLE') . "`;
        ";

        $result = $this->db->fetch($query);

        if ($result->num_rows == 0) {
            return $this->create($object);
        }

        $jsonEncoded = json_encode($object);
        $query = "
            UPDATE `" . $this->db->getTable('CONFIGURATION_TABLE') . "` set data = '{$jsonEncoded}';
        ";
        
        return $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        $query = "SELECT data FROM `" . $this->db->getTable('CONFIGURATION_TABLE') . "`";
        $query .= "WHERE id = 1;";

        $result = $this->db->fetch($query);

        $factory = new ConfigurationFactory();

        if (empty($result->row)) {
            return $factory->createEmpty();
        }

        return $factory->createFromJsonData($result->row['data']);
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findOrNew($objectId)
    {
        $object = $this->find($objectId);
        if ($object === null) {
            $factory = new ConfigurationFactory();
            $object = $factory->createEmpty();
        }

        return $object;
    }
}