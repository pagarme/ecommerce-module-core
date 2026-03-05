<?php

namespace Pagarme\Core\Test\Hub\Factories;

use Exception;
use Pagarme\Core\Hub\Commands\AbstractCommand;
use Pagarme\Core\Hub\Commands\CommandType;
use Pagarme\Core\Hub\Commands\UninstallCommand;
use Pagarme\Core\Hub\Commands\UpdateCommand;
use Pagarme\Core\Hub\Factories\HubCommandFactory;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\Id\AccountId;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\Id\MerchantId;
use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Kernel\ValueObjects\Key\PublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use PHPUnit\Framework\TestCase;
use stdClass;

class HubCommandFactoryTest extends TestCase
{
    /** @var HubCommandFactory */
    private $factory;

    /** @var stdClass */
    private $payload;

    public function setUp(): void
    {
        $this->factory = new HubCommandFactory();

        $this->payload = json_decode('{
            "access_token": "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
            "account_id": "acc_XXXXXXXXXXXXXXXX",
            "account_public_key": "pk_test_XXXXXXXXXXXXXXXX",
            "install_id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
            "merchant_id": "merch_XXXXXXXXXXXXXXXX",
            "paymentProfileId": "pp_XXXXXXXXXXXXXXXX",
            "poiType": ["Ecommerce"],
            "additional_data": {},
            "type": "Development",
            "actions": [],
            "events": [],
            "command": "Install"
        }');
    }

    public function testShouldCreateHubInstallCommand()
    {
        $hubInstall = $this->factory->createFromStdClass($this->payload);
        $this->assertInstanceOf(AbstractCommand::class, $hubInstall);
    }

    public function testHubDevelopmentShouldUsePKTest()
    {
        $hubInstall = $this->factory->createFromStdClass($this->payload);
        $this->assertEquals(CommandType::Development(), $hubInstall->getType());
        $this->assertInstanceOf(TestPublicKey::class, $hubInstall->getAccountPublicKey());
    }

    public function testHubSandboxShouldUsePKTest()
    {
        $this->payload->type = "Sandbox";
        $hubInstall = $this->factory->createFromStdClass($this->payload);
        $this->assertEquals(CommandType::Sandbox(), $hubInstall->getType());
        $this->assertInstanceOf(TestPublicKey::class, $hubInstall->getAccountPublicKey());
    }

    public function testHubProductionShouldUsePKLive()
    {
        $this->payload->account_public_key = "pk_XXXXXXXXXXXXXXXX";
        $this->payload->type = "Production";
        $hubInstall = $this->factory->createFromStdClass($this->payload);
        $this->assertEquals(CommandType::Production(), $hubInstall->getType());
        $this->assertInstanceOf(PublicKey::class, $hubInstall->getAccountPublicKey());
    }

    public function testShouldCreateHubUninstallCommand()
    {
        $this->payload->command = "Uninstall";
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertInstanceOf(UninstallCommand::class, $command);
        $this->assertInstanceOf(AbstractCommand::class, $command);
    }

    public function testShouldCreateHubUpdateCommand()
    {
        $this->payload->command = "Update";
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertInstanceOf(UpdateCommand::class, $command);
        $this->assertInstanceOf(AbstractCommand::class, $command);
    }

    public function testInvalidCommandShouldThrowException()
    {
        $this->expectException(Exception::class);
        $this->payload->command = "NonExistent";
        $this->factory->createFromStdClass($this->payload);
    }

    public function testFactoryPopulatesAccessToken()
    {
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertInstanceOf(HubAccessTokenKey::class, $command->getAccessToken());
        $this->assertEquals(
            $this->payload->access_token,
            $command->getAccessToken()->getValue()
        );
    }

    public function testFactoryPopulatesAccountId()
    {
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertInstanceOf(AccountId::class, $command->getAccountId());
        $this->assertEquals(
            $this->payload->account_id,
            $command->getAccountId()->getValue()
        );
    }

    public function testFactoryPopulatesMerchantId()
    {
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertInstanceOf(MerchantId::class, $command->getMerchantId());
        $this->assertEquals(
            $this->payload->merchant_id,
            $command->getMerchantId()->getValue()
        );
    }

    public function testFactoryPopulatesInstallId()
    {
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertInstanceOf(GUID::class, $command->getInstallId());
        $this->assertEquals(
            $this->payload->install_id,
            $command->getInstallId()->getValue()
        );
    }

    public function testFactoryPopulatesPaymentProfileId()
    {
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertEquals($this->payload->paymentProfileId, $command->getPaymentProfileId());
    }

    public function testFactoryPopulatesPoiType()
    {
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertIsArray($command->getPoiType());
        $this->assertEquals((array) $this->payload->poiType, $command->getPoiType());
    }

    public function testMissingPoiTypeResultsInEmptyArray()
    {
        unset($this->payload->poiType);
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertIsArray($command->getPoiType());
        $this->assertEmpty($command->getPoiType());
    }

    public function testEmptyPoiTypeResultsInEmptyArray()
    {
        $this->payload->poiType = [];
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertIsArray($command->getPoiType());
        $this->assertEmpty($command->getPoiType());
    }

    public function testMissingAccountIdResultsInNull()
    {
        unset($this->payload->account_id);
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertNull($command->getAccountId());
    }

    public function testMissingMerchantIdResultsInNull()
    {
        unset($this->payload->merchant_id);
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertNull($command->getMerchantId());
    }

    public function testMissingPaymentProfileIdResultsInNull()
    {
        unset($this->payload->paymentProfileId);
        $command = $this->factory->createFromStdClass($this->payload);
        $this->assertNull($command->getPaymentProfileId());
    }

    public function testInvalidPublicKeyFormatShouldThrowInvalidParamException()
    {
        $this->expectException(InvalidParamException::class);
        $this->payload->account_public_key = "pk_INVALIDO";
        $this->factory->createFromStdClass($this->payload);
    }

    public function testProductionKeyUsedInSandboxShouldThrowInvalidParamException()
    {
        $this->expectException(InvalidParamException::class);
        $this->payload->type = "Sandbox";
        $this->payload->account_public_key = "pk_XXXXXXXXXXXXXXXX";
        $this->factory->createFromStdClass($this->payload);
    }

    public function testTestKeyUsedInProductionShouldThrowInvalidParamException()
    {
        $this->expectException(InvalidParamException::class);
        $this->payload->type = "Production";
        $this->payload->account_public_key = "pk_test_XXXXXXXXXXXXXXXX";
        $this->factory->createFromStdClass($this->payload);
    }

    public function testAccessTokenWithLessThan64CharsShouldThrowInvalidParamException()
    {
        $this->expectException(InvalidParamException::class);
        $this->payload->access_token = "TOKEN_CURTO_INVALIDO";
        $this->factory->createFromStdClass($this->payload);
    }
}
