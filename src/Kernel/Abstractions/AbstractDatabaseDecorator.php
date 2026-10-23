<?php

namespace Pagarme\Core\Kernel\Abstractions;

abstract class AbstractDatabaseDecorator
{
    const TABLE_MODULE_CONFIGURATION = 0;
    const TABLE_WEBHOOK = 1;
    const TABLE_ORDER = 2;
    const TABLE_CHARGE = 3;
    const TABLE_TRANSACTION = 4;
    const TABLE_HUB_INSTALL_TOKEN = 5;
    const TABLE_SAVED_CARD = 6;
    const TABLE_CUSTOMER = 7;
    const TABLE_RECURRENCE_PRODUCTS_PLAN = 8;
    const TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION = 9;
    const TABLE_RECURRENCE_SUB_PRODUCTS = 10;
    const TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS = 11;
    const TABLE_RECURRENCE_CHARGE = 12;
    const TABLE_RECURRENCE_SUBSCRIPTION = 13;
    const TABLE_RECURRENCE_SUBSCRIPTION_ITEM = 14;
    const TABLE_RECIPIENTS = 15;

    protected $db;
    protected $tablePrefix;
    protected $tableArray;
    public function __construct($dbObject)
    {
        $this->db = $dbObject;
        $this->setTablePrefix();
        $this->setTableArray();
    }
    /**
     * @param string $query SQL with named placeholders (":name")
     * @param array $bind values bound to the placeholders, never interpolated
     */
    public function query($query, array $bind = [])
    {
        $this->doQuery($query, $bind);
    }
    public function fetch($query, array $bind = [])
    {
        $queryResult = $this->doFetch($query, $bind);
        return $this->formatResults($queryResult);
    }
    public function getTable($tableName)
    {
        if (isset($this->tableArray[$tableName])) {
            return $this->tableArray[$tableName];
        }
        throw new \Exception("Table name '$tableName' not found!");
    }
    abstract public function getLastId();
    abstract protected function setTableArray();
    abstract protected function setTablePrefix();
    abstract protected function doQuery($query, array $bind);

    /**
     *
     * @return array
     */
    abstract protected function doFetch($query, array $bind);
    abstract protected function formatResults($query);
}
