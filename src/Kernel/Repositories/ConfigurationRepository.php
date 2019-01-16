<?php

namespace Mundipagg\Core\Kernel\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\Factories\ConfigurationFactory;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class ConfigurationRepository extends AbstractRepository
{
    protected function create(AbstractEntity &$object)
    {
        $jsonEncoded = json_encode($object);
        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);
        
        $query = "INSERT INTO `$configTable` (data) VALUES ('$jsonEncoded')";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);
        $query = " SELECT * FROM `$configTable`;";

        $result = $this->db->fetch($query);

        if ($result->num_rows == 0) {
            return $this->create($object);
        }

        $jsonEncoded = json_encode($object);
        $query = "
            UPDATE `$configTable` set data = '{$jsonEncoded}';
        ";
        
        return $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);

        $query = "SELECT data FROM `$configTable` WHERE id = 1;";

        $result = $this->db->fetch($query);

        $factory = new ConfigurationFactory();

        if (empty($result->row)) {
            return null;
        }

        return $factory->createFromJsonData($result->row['data']);
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        // TODO: Implement findByMundipaggId() method.
    }
}