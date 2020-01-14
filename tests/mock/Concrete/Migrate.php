<?php


namespace Mundipagg\Core\Test\Mock\Concrete;


class Migrate
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setUpConfiguration()
    {
        $this->runConfigurationMigration();
    }

    public function up()
    {
        $this->upRecurrenceProductSubscription();
        $this->upRecurrenceSubscriptionRepetitions();
        $this->upRecurrenceSubProduct();
    }

    public function down()
    {
        $this->downRecurrenceProductSubscription();
        $this->downRecurrenceSubscriptionRepetitions();
        $this->downRecurrenceSubProduct();
        $this->downConfigurationMigration();
    }

    public function runConfigurationMigration()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS mundipagg_module_core_configuration (
                      id INTEGER PRIMARY KEY, 
                      data TEXT, 
                      store_id TEXT)");

        $insert = "INSERT INTO mundipagg_module_core_configuration (data, store_id) 
                VALUES  (:data, :store_id)";

        $stmt = $this->db->prepare($insert);

        $config = json_encode([
           "enabled" => true
        ]);

        $stmt->bindValue(':data', $config, SQLITE3_TEXT);
        $stmt->bindValue(':store_id', '1', SQLITE3_TEXT);

        $stmt->execute();
    }

    public function downConfigurationMigration()
    {
        $this->db->exec("DROP TABLE mundipagg_module_core_configuration");
    }

    public function upRecurrenceProductSubscription()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS mundipagg_module_core_recurrence_products_subscription (
                      id INTEGER PRIMARY KEY, 
                      product_id INTEGER NULLABLE , 
                      credit_card TEXT NULLABLE, 
                      allow_installments TEXT NULLABLE, 
                      boleto BOOLEAN NULLABLE, 
                      sell_as_normal_product TEXT NULLABLE, 
                      cycles INTEGER NULLABLE, 
                      billing_type TEXT NULLABLE NULLABLE, 
                      created_at TIMESTAMP, 
                      updated_at TIMESTAMP)");

    }

    public function downRecurrenceProductSubscription()
    {
        $this->db->exec("DROP TABLE mundipagg_module_core_recurrence_products_subscription");
    }

    public function upRecurrenceSubscriptionRepetitions()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS mundipagg_module_core_recurrence_subscription_repetitions (
                      id INTEGER PRIMARY KEY, 
                      subscription_id INTEGER NULLABLE , 
                      `interval` TEXT NULLABLE, 
                      interval_count INTEGER NULLABLE, 
                      recurrence_price INTEGER NULLABLE,
                      created_at TIMESTAMP, 
                      updated_at TIMESTAMP)");
    }

    public function downRecurrenceSubscriptionRepetitions()
    {
        $this->db->exec("DROP TABLE mundipagg_module_core_recurrence_subscription_repetitions");
    }

    public function upRecurrenceSubProduct()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS mundipagg_module_core_recurrence_sub_products (
                      id INTEGER PRIMARY KEY,
                      product_id INTEGER,
                      product_recurrence_id INTEGER,
                      recurrence_type TEXT,
                      cycles INTEGER NULLABLE,
                      quantity INTEGER NULLABLE,
                      trial_period_days INTEGER NULLABLE,
                      created_at TIMESTAMP,
                      updated_at TIMESTAMP)");
    }

    public function downRecurrenceSubProduct()
    {
        $this->db->exec("DROP TABLE mundipagg_module_core_recurrence_sub_products");
    }
}