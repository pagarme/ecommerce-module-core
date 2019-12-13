<?php

namespace Mundipagg\Core\Test\Payment\Respository;

use Exception;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\Repositories\SavedCardRepository;
use Mundipagg\Core\Test\Mock\Connection\BaseTestConnection;
use Mundipagg\Core\Test\Mock\MockAbstractModuleCoreSetup;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class SavedCardRepositoryTests
{
    /**
     * @throws InvalidParamException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testeH()
    {

//        $memory_db = new PDO('sqlite::memory:');
//        // Set errormode to exceptions
//        $memory_db->setAttribute(PDO::ATTR_ERRMODE,
//            PDO::ERRMODE_EXCEPTION);
//
//        $memory_db->exec("CREATE TABLE mundipagg_module_core_saved_card (
//                      id INTEGER PRIMARY KEY,
//                      title TEXT,
//                      message TEXT,
//                      time TEXT)");
//
//
//        $insert = "INSERT INTO mundipagg_module_core_saved_card (id, title, message, time)
//                VALUES (1, 'teste title', 'message content', '20:20:00')";
//
//        $stmt = $memory_db->prepare($insert);
//        $stmt->execute();
//
//        $result = $memory_db->query('SELECT * FROM messages');
//
//        var_dump($result->fetchAll());


        // die;

        //  $testClass     = new ReflectionClass('TestClass');
        //    $abstractClass = new ReflectionClass(AbstractModuleCoreSetup::class);
//
        $r = (new class extends AbstractModuleCoreSetup
        {

            public function setConfig()
            {
                return '1456';
            }

            public function setModuleVersion()
            {
                return '1789';
            }

            public function setPlatformVersion()
            {
                return '4891';
            }

            public function setLogPath()
            {
                return '6541';
            }

            public function loadModuleConfigurationFromPlatform()
            {
                // TODO: Implement loadModuleConfigurationFromPlatform() method.
            }

            public static function getDatabaseAccessObject()
            {
                // TODO: Implement getDatabaseAccessObject() method.
            }

            /**
             *
             * @return string
             **/
            protected static function getPlatformHubAppPublicAppKey()
            {
                // TODO: Implement getPlatformHubAppPublicAppKey() method.
            }

            protected function _getDashboardLanguage()
            {
                // TODO: Implement _getDashboardLanguage() method.
            }

            protected function _getStoreLanguage()
            {
                // TODO: Implement _getStoreLanguage() method.
            }

            protected function _formatToCurrency($price)
            {
                // TODO: Implement _formatToCurrency() method.
            }

            /**
             * @since 1.6.1
             */
            public static function getCurrentStoreId()
            {
                // TODO: Implement getCurrentStoreId() method.
            }

            /**
             * @since 1.6.1
             */
            public static function getDefaultStoreId()
            {
                // TODO: Implement getDefaultStoreId() method.
            }

            /**
             * @return \DateTimeZone
             * @since 1.7.1
             *
             */
            protected function getPlatformStoreTimezone()
            {
                // TODO: Implement getPlatformStoreTimezone() method.
            }
        });
//
//        $class = new ReflectionClass(AbstractModuleCoreSetup::class);
//
//        $propertyInstance = $class->getProperty('instance');
//        $propertyInstance->setAccessible(true);
//        $propertyInstance->setValue(new MockAbstractModuleCoreSetup());
//
//
//        $propertyConfig = $class->getProperty('config');
//        $propertyConfig->setAccessible(true);
//        $propertyConfig->setValue(
//            [
//                0 => 'Mundipagg\Core\Test\Mock\Magento2',
//                11 => 'Mundipagg\Core\Test\Mock\Magento2Database'
//            ]
//        );



     //   $savedCardRepository = new SavedCardRepository();
    //    $ob = $savedCardRepository->findByOwnerId(new CustomerId('cus_1234567891234567'));
   //     die;
     //   var_dump($ob); die('jdkljdkld');
    }
}

