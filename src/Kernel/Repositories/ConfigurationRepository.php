<?php

namespace Pagarme\Core\Kernel\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Factories\ConfigurationFactory;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class ConfigurationRepository extends AbstractRepository
{
    private $pattern = '/\\\s+\\\s\\\r\\\n|\\\r|\\\n\\\r|\\\n/m';

    /**
     * @param string $jsonEncoded
     * @return string
     */
    private function removeSpecialCharacters($jsonEncoded)
    {
        return trim(
            preg_replace(
                $this->pattern,
                ' ',
                $jsonEncoded ?? ''
            )
        );
    }

    protected function create(AbstractEntity &$object)
    {
        $jsonEncoded = json_encode($object);

        $jsonEncoded = $this->removeSpecialCharacters($jsonEncoded);
        $preparedObject = json_decode($jsonEncoded);
        $preparedObject->parent = null;
        $jsonEncoded = json_encode($preparedObject);

        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);
        
        $query = "INSERT INTO `$configTable` (data, store_id) VALUES (:data, :store_id)";

        $this->db->query($query, [
            'data' => $jsonEncoded,
            'store_id' => $object->getStoreId(),
        ]);
    }

    protected function update(AbstractEntity &$object)
    {
        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);
        $query = "SELECT * FROM `$configTable` WHERE id = :id;";

        $result = $this->db->fetch($query, ['id' => $object->getId()]);

        if ($result->num_rows == 0) {
            return $this->create($object);
        }

        $jsonEncoded = json_encode($object);
        $jsonEncoded = $this->removeSpecialCharacters($jsonEncoded);

        $preparedObject = json_decode($jsonEncoded);
        $preparedObject->parent = null;
        $jsonEncoded = json_encode($preparedObject);

        $query = "
            UPDATE `$configTable` set data = :data
            WHERE id = :id;
        ";

        return $this->db->query($query, [
            'data' => $jsonEncoded,
            'id' => $object->getId(),
        ]);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);
        $objectId = filter_var($objectId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT data, id FROM `$configTable` WHERE id = :id;";

        $result = $this->db->fetch($query, ['id' => $objectId]);

        $factory = new ConfigurationFactory();

        if (empty($result->row)) {
            return null;
        }

        $config =  $factory->createFromJsonData($result->row['data']);
        $config->setId($result->row['id']);

        return $config;
    }

    public function findByStore($storeId)
    {
        if ($storeId === null) {
            return null;
        }

        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);
        $storeId = filter_var($storeId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT data, id FROM `$configTable` WHERE store_id = :store_id;";

        $result = $this->db->fetch($query, ['store_id' => $storeId]);

        $factory = new ConfigurationFactory();

        if (empty($result->row)) {
            return null;
        }

        $config = $factory->createFromJsonData($result->row['data']);
        $config->setId($result->row['id']);

        return $config;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        // TODO: Implement findByPagarmeId() method.
    }
}