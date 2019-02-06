<?php

namespace Mundipagg\Core\Hub\Factories;

use Mundipagg\Core\Hub\Commands\AbstractCommand;
use Mundipagg\Core\Hub\Commands\CommandType;
use Mundipagg\Core\Kernel\ValueObjects\Id\AccountId;
use Mundipagg\Core\Kernel\ValueObjects\Id\GUID;
use Mundipagg\Core\Kernel\ValueObjects\Id\MerchantId;
use Mundipagg\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\PublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\TestPublicKey;
use ReflectionClass;

class HubCommandFactory
{
    /**
     * @param $object
     * @return AbstractCommand
     * @throws \ReflectionException
     */
    public function createFromStdClass($object)
    {
        $commandClass = (new ReflectionClass(AbstractCommand::class))->getNamespaceName();
        $commandClass .= "\\" . $object->command . "Command";

        if (!class_exists($commandClass)) {
            throw new \Exception("Invalid Command class! $commandClass");
        }

        /** @var AbstractCommand $command */
        $command = new $commandClass();

        $command->setAccessToken(
            new HubAccessTokenKey($object->accessToken)
        );
        $command->setAccountId(
            new AccountId($object->accountId)
        );

        $type = $object->type;
        $command->setType(
            CommandType::$type()
        );

        $publicKeyClass = PublicKey::class;
        if ($command->getType()->equals(CommandType::Sandbox())) {
            $publicKeyClass = TestPublicKey::class;
        }

        $command->setAccountPublicKey(
            new $publicKeyClass($object->accountPublicKey)
        );

        $command->setInstallId(
            new GUID($object->installId)
        );

        $command->setMerchantId(
            new MerchantId($object->merchantId)
        );

        return $command;
    }
}