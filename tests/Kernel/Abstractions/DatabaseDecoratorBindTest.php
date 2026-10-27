<?php

namespace Pagarme\Core\Test\Kernel\Abstractions;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Test\Abstractions\AbstractSetupTest;

class DatabaseDecoratorBindTest extends AbstractSetupTest
{
    /**
     * @var AbstractDatabaseDecorator
     */
    private $db;

    public function setUp(): void
    {
        parent::setUp();
        $this->db = MPSetup::getDatabaseAccessDecorator();
    }

    public function testShouldStoreValueWithSingleQuoteAsLiteralWhenBound()
    {
        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);

        $this->db->query(
            "INSERT INTO `$configTable` (data, store_id) VALUES (:data, :store)",
            ['data' => "O'Reilly", 'store' => 0]
        );

        $result = $this->db->fetch(
            "SELECT data FROM `$configTable` WHERE data = :data",
            ['data' => "O'Reilly"]
        );

        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals("O'Reilly", $result->row["data"]);
    }

    public function testShouldNotInterpretSqlPayloadInBoundValue()
    {
        $configTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION);
        $payload = "x'); DELETE FROM `$configTable`; --";

        $this->db->query(
            "INSERT INTO `$configTable` (data, store_id) VALUES (:data, :store)",
            ['data' => $payload, 'store' => 0]
        );

        $result = $this->db->fetch(
            "SELECT data FROM `$configTable` WHERE data = :data",
            ['data' => $payload]
        );

        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals($payload, $result->row["data"]);
    }
}
