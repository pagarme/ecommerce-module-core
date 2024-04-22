<?php

namespace Pagarme\Core\Test\Marketplace\Factories;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Marketplace\Factories\RecipientFactory;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface;

class RecipientFactoryTest extends TestCase
{
    public function testCreateFromPostDataShouldCreateWithWebhookData()
    {
        $pagarmeId = "rp_xxxxxxxxxxxxxxxx";
        $postData = [
            "id" => $pagarmeId,
            "name" => "Test recipient",
            "email" => "test@recipient.test",
            "document" => "11111111111",
            "description" => "Test description",
            "type" => "individual",
            "payment_mode" => "bank_transfer",
            "status" => "active",
            "kyc_details" =>
            [
                "status" => "approved"
            ],
        ];

        $recipientFactory = new RecipientFactory();

        $result = $recipientFactory->createFromPostData($postData);
        $this->assertSame($result->getStatus(), RecipientInterface::ACTIVE);
        $this->assertSame($result->getPagarmeId()->getValue(), $pagarmeId);
    }

    public function testCreateFromDbDataShouldCreateWithStatus()
    {
        $dbData = [
            "id" => 1,
            "external_id" => 2,
            "name" => "Test recipient",
            "email" => "test@recipient.test",
            "document" => "11111111111",
            "type" => "cpf",
            "pagarme_id" => "rp_xxxxxxxxxxxxxxxx",
            "status" => RecipientInterface::ACTIVE,
        ];

        $recipientFactory = new RecipientFactory();

        $result = $recipientFactory->createFromDbData($dbData);
        $this->assertSame($result->getStatus(), RecipientInterface::ACTIVE);
    }
}
