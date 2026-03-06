<?php

namespace Hub\Commands;

use Exception;
use Pagarme\Core\Hub\Commands\AbstractCommand;
use Pagarme\Core\Hub\Commands\CommandType;
use Pagarme\Core\Hub\Commands\InstallCommand;
use Pagarme\Core\Hub\Commands\UninstallCommand;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\ValueObjects\Id\AccountId;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\Id\MerchantId;
use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Pagarme\Core\Test\Abstractions\AbstractSetupTest;

class UninstallCommandTest extends AbstractSetupTest
{
    /**
     * @var UninstallCommand
     */
    private $uninstallCommand;

    /**
     * @var HubAccessTokenKey
     */
    private $accessToken;

    public function setUp(): void
    {
        parent::setUp();

        $accessToken  = new HubAccessTokenKey(str_repeat('x', 64));
        $accountId    = new AccountId('acc_xxxxxxxxxxxxxxxx');
        $merchantId   = new MerchantId('merch_xxxxxxxxxxxxxxxx');
        $installId    = new GUID('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
        $publicKey    = new TestPublicKey('pk_test_xxxxxxxxxxxxxxxx');
        $type         = CommandType::Sandbox();

        $this->accessToken = $accessToken;

        $installCommand = new InstallCommand();
        $installCommand
            ->setAccessToken($accessToken)
            ->setAccountId($accountId)
            ->setMerchantId($merchantId)
            ->setInstallId($installId)
            ->setAccountPublicKey($publicKey)
            ->setType($type);
        $installCommand->setPaymentProfileId('pp_123');
        $installCommand->setPoiType(['Ecommerce']);
        $installCommand->execute();

        $this->uninstallCommand = new UninstallCommand();
        $this->uninstallCommand->setAccessToken($accessToken);
    }

    public function testUninstallCommandIsInstanceOfAbstractCommand()
    {
        $this->assertInstanceOf(AbstractCommand::class, $this->uninstallCommand);
    }

    public function testExecuteThrowsExceptionWhenHubIsNotInstalled()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Hub is not installed!");

        parent::setUp();

        $command = new UninstallCommand();
        $command->setAccessToken($this->accessToken);
        $command->execute();
    }

    public function testExecuteThrowsExceptionWhenAccessTokenDoesNotMatchSecretKey()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Access Denied.");

        $wrongToken = new HubAccessTokenKey(str_repeat('y', 64));

        $command = new UninstallCommand();
        $command->setAccessToken($wrongToken);
        $command->execute();
    }

    /**
     * `getMethodsInherited()` returns `[]` when `parentConfiguration` is `null`
     * (see `Configuration::getMethodsInherited`). The clean config produced by
     * `UninstallCommand` has no parent, so the observable contract is that the
     * inherited-method list is empty after uninstall, while the Hub-specific
     * guard methods (`getSecretKey`, `getPublicKey`, `isHubEnabled`) are still
     * callable directly on the object without delegation.
     */
    public function testExecuteInheritedMethodsListIsEmptyBecauseNoParentExists()
    {
        $this->uninstallCommand->execute();

        $config  = MPSetup::getModuleConfiguration();
        $methods = $config->getMethodsInherited();

        $this->assertIsArray($methods);
        $this->assertEmpty($methods);
    }

    public function testExecuteDoesNotDuplicateInheritedMethods()
    {
        $this->uninstallCommand->execute();

        $config  = MPSetup::getModuleConfiguration();
        $methods = $config->getMethodsInherited();

        $this->assertEquals(array_unique($methods), $methods);
    }

    public function testExecutePersistsCleanConfigurationToRepository()
    {
        $this->uninstallCommand->execute();

        $config = MPSetup::getModuleConfiguration();

        $this->assertFalse($config->isHubEnabled());
        $this->assertNull($config->getHubInstallId());
        $this->assertNull($config->getAccountId());
        $this->assertNull($config->getMerchantId());
        $this->assertNull($config->getPaymentProfileId());
        $this->assertEmpty($config->getPoiType());
    }

    public function testExecuteThrowsExceptionOnSecondCallAfterSuccessfulUninstall()
    {
        $this->uninstallCommand->execute();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Hub is not installed!");

        $secondCommand = new UninstallCommand();
        $secondCommand->setAccessToken($this->accessToken);
        $secondCommand->execute();
    }
}
