<?php

namespace Mundipagg\Core\Test\Hub\Aggregates;

use Mundipagg\Core\Hub\Aggregates\InstallToken;
use Mundipagg\Core\Hub\ValueObjects\HubInstallToken;
use PHPUnit\Framework\TestCase;

class InstallTokenTests extends TestCase
{
    /**
     * @var InstallToken
     */
    public $installToken;
    /**
     * @var HubInstallToken
     */
    public $hubInstallToken;

    public function setUp(): void
    {
        $token = hash('sha512', '1' . '|' . microtime());
        $this->hubInstallToken = new HubInstallToken($token);

        $lifeSpam = InstallToken::LIFE_SPAN;
        $createdTime = time();
        $expireTime = $createdTime + $lifeSpam;

        $this->installToken = new InstallToken();
        $this->installToken->setToken($this->hubInstallToken);
        $this->installToken->setUsed(false);
        $this->installToken->setCreatedAtTimestamp($createdTime);
        $this->installToken->setExpireAtTimestamp($expireTime);
    }

    public function testInstallTokenBeCreated()
    {
        $this->assertInstanceOf(InstallToken::class, $this->installToken);
    }

    public function testInstallTokenMethodGetToken()
    {
        $this->assertInstanceOf(HubInstallToken::class, $this->installToken->getToken());
    }

    public function testInstallTokenMethodIsUsed()
    {
        $this->assertIsBool($this->installToken->isUsed());
    }
}
