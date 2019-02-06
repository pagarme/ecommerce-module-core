<?php

namespace Mundipagg\Core\Hub\Factories;

use Mundipagg\Core\Kernel\GatewayId\AccountId;
use Mundipagg\Core\Kernel\GatewayKey\HubAccessTokenKey;
use Mundipagg\Core\Kernel\GatewayKey\PublicKey;
use Mundipagg\Core\Kernel\GatewayKey\TestPublicKey;
use Mundipagg\Core\Kernel\GatewayId\GUID;
use Mundipagg\Core\Kernel\GatewayId\MerchantId;
use Mundipagg\Core\Hub\Commands\AbstractCommand;
use Mundipagg\Core\Hub\Commands\CommandType;
use ReflectionClass;

class CommandFactory
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