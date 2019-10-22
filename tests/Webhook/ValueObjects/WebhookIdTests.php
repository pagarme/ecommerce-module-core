<?php

namespace Mundipagg\Core\Test\Webhook\ValueObjects;

use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Webhook\ValueObjects\WebhookId;
use PHPUnit\Framework\TestCase;

class WebhookIdTests extends TestCase
{
    public function testValidateValue()
    {
        $webhookId = new WebhookId("hook_" . str_repeat('a', 16));
        $this->assertEquals("hook_" . str_repeat('a', 16), $webhookId->getValue());
    }

    public function testValueIsNotValid()
    {
        $this->expectException(InvalidParamException::class);
        $this->expectExceptionMessage(
            "Invalid value for " . WebhookId::class . "! Passed value: hook_"
        );

        new WebhookId("hook_");
    }
}
