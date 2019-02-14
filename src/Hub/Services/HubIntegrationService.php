<?php

namespace Mundipagg\Core\Hub\Services;

use Mundipagg\Core\Hub\Aggregates\InstallToken;
use Mundipagg\Core\Hub\Factories\HubCommandFactory;
use Mundipagg\Core\Hub\Factories\InstallTokenFactory;
use Mundipagg\Core\Hub\Repositories\InstallTokenRepository;
use Mundipagg\Core\Hub\ValueObjects\HubInstallToken;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Unirest\Request;

final class HubIntegrationService
{
    /**
     *
     * @param  $installSeed
     * @return \Mundipagg\Core\Hub\ValueObjects\HubInstallToken
     */
    public function startHubIntegration($installSeed)
    {
        $tokenRepo = new InstallTokenRepository();

        $enabledTokens = $tokenRepo->listEntities(0, false);

        //expire all tokens
        foreach ($enabledTokens as $enabledToken) {
            $enabledToken->setExpireAtTimestamp(
                $enabledToken->getCreatedAtTimestamp() - 1000
            );
            $tokenRepo->save($enabledToken);
        }

        $installFactory = new InstallTokenFactory();
        $installToken = $installFactory->createFromSeed($installSeed);

        $tokenRepo->save($installToken);

        return $installToken->getToken();
    }

    public function endHubIntegration(
        $installToken,
        $authorizationCode,
        $hubCallbackUrl = null,
        $webhookUrl = null
    ) {
        $tokenRepo = new InstallTokenRepository();

        $installToken = $tokenRepo->findByMundipaggId(new HubInstallToken($installToken));

        if (is_a($installToken, InstallToken::class) 
            && !$installToken->isExpired() 
            && !$installToken->isUsed()
        ) {
            $body = [
                "code" => $authorizationCode
            ];

            if ($hubCallbackUrl) {
                $body['hubCallbackUrl'] = $hubCallbackUrl;
            }

            if ($webhookUrl) {
                $body['webhookUrl'] = $webhookUrl;
            }

            $url = 'https://hubapi.mundipagg.com/auth/apps/access-tokens';
            $headers = [
                'PublicAppKey' => '5f826207-5e4e-42c2-be49-e69b8d4da233',
                'Content-Type' => 'application/json'
            ];

            $result = Request::post(
                $url,
                $headers,
                json_encode($body)
            );

            if ($result->code === 201) {
                $this->executeCommandFromPost($result->body);

                //if its ok
                $installToken->setUsed(true);
                $tokenRepo->save($installToken);
            }
        }
    }

    public function getHubStatus()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        return $moduleConfig->isHubEnabled() ? 'enabled' : 'disabled';
    }

    public function executeCommandFromPost($body)
    {
        $commandFactory = new HubCommandFactory();
        $command = $commandFactory->createFromStdClass($body);
        $command->execute();
    }
}