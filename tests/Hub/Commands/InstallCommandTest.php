<?php

namespace Pagarme\Core\Test\Hub\Commands;

use Pagarme\Core\Hub\Commands\AbstractCommand;
use Pagarme\Core\Hub\Commands\CommandType;
use Pagarme\Core\Hub\Commands\InstallCommand;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\ValueObjects\Id\AccountId;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\Id\MerchantId;
use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Pagarme\Core\Test\Abstractions\AbstractSetupTest;

class InstallCommandTest extends AbstractSetupTest
{
    /**
     * @var InstallCommand
     */
    private $command;

    private $accessToken;
    private $accountId;
    private $merchantId;
    private $installId;
    private $publicKey;
    private $type;

    public function setUp(): void
    {
        parent::setUp();

        $this->accessToken = new HubAccessTokenKey(
            str_repeat('x', 64)
        );
        $this->accountId   = new AccountId('acc_xxxxxxxxxxxxxxxx');
        $this->merchantId  = new MerchantId('merch_xxxxxxxxxxxxxxxx');
        $this->installId   = new GUID('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
        $this->publicKey   = new TestPublicKey('pk_test_xxxxxxxxxxxxxxxx');
        $this->type        = CommandType::Sandbox();

        $this->command = new InstallCommand();
        $this->command
            ->setAccessToken($this->accessToken)
            ->setAccountId($this->accountId)
            ->setMerchantId($this->merchantId)
            ->setInstallId($this->installId)
            ->setAccountPublicKey($this->publicKey)
            ->setType($this->type);
        $this->command->setPaymentProfileId('pp_123');
        $this->command->setPoiType(['Ecommerce']);
    }

    public function testInstallCommandIsInstanceOfAbstractCommand()
    {
        $this->assertInstanceOf(AbstractCommand::class, $this->command);
    }

    public function testExecuteSavesConfigurationSuccessfully()
    {
        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertNotNull($config);
    }

    public function testExecutePersistsAccountId()
    {
        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertInstanceOf(AccountId::class, $config->getAccountId());
        $this->assertTrue($this->accountId->equals($config->getAccountId()));
    }

    public function testExecutePersistsMerchantId()
    {
        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertInstanceOf(MerchantId::class, $config->getMerchantId());
        $this->assertTrue($this->merchantId->equals($config->getMerchantId()));
    }

    public function testExecutePersistsPaymentProfileId()
    {
        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertEquals('pp_123', $config->getPaymentProfileId());
    }

    public function testExecuteWithNullPaymentProfileId()
    {
        $this->command->setPaymentProfileId(null);

        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertNull($config->getPaymentProfileId());
    }

    public function testExecutePersistsPoiType()
    {
        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertIsArray($config->getPoiType());
        $this->assertEquals(['Ecommerce'], $config->getPoiType());
    }

    public function testExecutePersistsInstallId()
    {
        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertInstanceOf(GUID::class, $config->getHubInstallId());
        $this->assertTrue($this->installId->equals($config->getHubInstallId()));
    }

    public function testExecutePersistsHubEnvironment()
    {
        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertEquals($this->type, $config->getHubEnvironment());
    }

    public function testExecutePersistsTestPublicKey()
    {
        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertInstanceOf(TestPublicKey::class, $config->getPublicKey());
        $this->assertTrue($this->publicKey->equals($config->getPublicKey()));
    }

    public function testExecutePersistsSecretKey()
    {
        $this->command->execute();

        $config = MPSetup::getModuleConfiguration();
        $this->assertInstanceOf(HubAccessTokenKey::class, $config->getSecretKey());
        $this->assertTrue($this->accessToken->equals($config->getSecretKey()));
    }
}
