<?php

namespace Pagarme\Core\Test\Hub\Commands;

use Pagarme\Core\Hub\Commands\AbstractCommand;
use Pagarme\Core\Hub\Commands\CommandType;
use Pagarme\Core\Kernel\ValueObjects\Id\AccountId;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\Id\MerchantId;
use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Kernel\ValueObjects\Key\PublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use PHPUnit\Framework\TestCase;

class AbstractCommandTest extends TestCase
{
    /**
     * @var AbstractCommand
     */
    private $command;

    public function setUp(): void
    {
        $this->command = $this->getMockForAbstractClass(AbstractCommand::class);
    }

    public function testPoiTypeIsEmptyArrayOnConstruction()
    {
        $this->assertIsArray($this->command->getPoiType());
        $this->assertEmpty($this->command->getPoiType());
    }

    public function testSetAndGetAccessToken()
    {
        $token = new HubAccessTokenKey(
            str_repeat('x', 64)
        );

        $this->command->setAccessToken($token);

        $this->assertInstanceOf(HubAccessTokenKey::class, $this->command->getAccessToken());
        $this->assertEquals($token, $this->command->getAccessToken());
    }

    public function testSetAndGetAccountId()
    {
        $accountId = new AccountId('acc_xxxxxxxxxxxxxxxx');

        $this->command->setAccountId($accountId);

        $this->assertInstanceOf(AccountId::class, $this->command->getAccountId());
        $this->assertEquals($accountId, $this->command->getAccountId());
    }

    public function testSetAndGetPaymentProfileIdWithString()
    {
        $this->command->setPaymentProfileId('pp_123');

        $this->assertIsString($this->command->getPaymentProfileId());
        $this->assertEquals('pp_123', $this->command->getPaymentProfileId());
    }

    public function testSetPaymentProfileIdAcceptsNull()
    {
        $this->command->setPaymentProfileId(null);

        $this->assertNull($this->command->getPaymentProfileId());
    }

    public function testSetAndGetPoiType()
    {
        $poiType = ['Ecommerce', 'ManualEntry'];

        $this->command->setPoiType($poiType);

        $this->assertIsArray($this->command->getPoiType());
        $this->assertEquals($poiType, $this->command->getPoiType());
    }

    public function testSetAccountPublicKeyWithTestPublicKey()
    {
        $key = new TestPublicKey('pk_test_xxxxxxxxxxxxxxxx');

        $this->command->setAccountPublicKey($key);

        $this->assertInstanceOf(TestPublicKey::class, $this->command->getAccountPublicKey());
        $this->assertEquals($key, $this->command->getAccountPublicKey());
    }

    public function testSetAccountPublicKeyWithPublicKey()
    {
        $key = new PublicKey('pk_xxxxxxxxxxxxxxxx');

        $this->command->setAccountPublicKey($key);

        $this->assertInstanceOf(PublicKey::class, $this->command->getAccountPublicKey());
        $this->assertEquals($key, $this->command->getAccountPublicKey());
    }

    public function testSetAndGetInstallId()
    {
        $guid = new GUID('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');

        $this->command->setInstallId($guid);

        $this->assertInstanceOf(GUID::class, $this->command->getInstallId());
        $this->assertEquals($guid, $this->command->getInstallId());
    }

    public function testSetAndGetMerchantId()
    {
        $merchantId = new MerchantId('merch_xxxxxxxxxxxxxxxx');

        $this->command->setMerchantId($merchantId);

        $this->assertInstanceOf(MerchantId::class, $this->command->getMerchantId());
        $this->assertEquals($merchantId, $this->command->getMerchantId());
    }

    public function testSetAndGetType()
    {
        $type = CommandType::Sandbox();

        $this->command->setType($type);

        $this->assertInstanceOf(CommandType::class, $this->command->getType());
        $this->assertEquals($type, $this->command->getType());
    }

    public function testSettersReturnSelf()
    {
        $token = new HubAccessTokenKey(str_repeat('x', 64));
        $account = new AccountId('acc_xxxxxxxxxxxxxxxx');
        $key = new TestPublicKey('pk_test_xxxxxxxxxxxxxxxx');
        $guid = new GUID('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
        $merch = new MerchantId('merch_xxxxxxxxxxxxxxxx');
        $type = CommandType::Development();

        $result = $this->command
            ->setAccessToken($token)
            ->setAccountId($account)
            ->setAccountPublicKey($key)
            ->setInstallId($guid)
            ->setMerchantId($merch)
            ->setType($type);

        $this->assertInstanceOf(AbstractCommand::class, $result);
    }
}
