<?php

namespace Pagarme\Core\Test\Marketplace\Factories;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Marketplace\Factories\RecipientFactory;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface;

class RecipientFactoryTest extends TestCase
{
    /**
     * @dataProvider webhookDataProvider
     */
    public function testCreateFromPostDataShouldCreateWithWebhookData($webhookData)
    {
        // Arrange
        $recipientFactory = new RecipientFactory();

        // Act
        $result = $recipientFactory->createFromPostData($webhookData);

        // Assert
        $this->assertSame($result->getStatus(), RecipientInterface::ACTIVE);
        $this->assertSame($result->getPagarmeId()->getValue(), $webhookData['id']);
    }

    public function testCreateFromDbDataShouldCreateWithStatus()
    {
        // Arrange
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

        // Act
        $result = $recipientFactory->createFromDbData($dbData);

        // Assert
        $this->assertSame($result->getStatus(), RecipientInterface::ACTIVE);
    }

    public function testAlphanumericCnpjFromPostDataShouldBeClassifiedAsCompany()
    {
        // Arrange
        $postData = [
            "id" => 'rp_xxxxxxxxxxxxxxxx',
            "name" => "Test company",
            "email" => "test@company.test",
            "document" => "1A2B3C4D000195",
            "type" => "company",
            "payment_mode" => "bank_transfer",
        ];
        $recipientFactory = new RecipientFactory();

        // Act
        $result = $recipientFactory->createFromPostData($postData);

        // Assert
        $this->assertSame('company', $result->getType());
    }

    public function testAlphanumericCnpjFromDbDataShouldBeClassifiedAsCompany()
    {
        // Arrange
        $dbData = [
            "id" => 1,
            "external_id" => 2,
            "name" => "Test company",
            "email" => "test@company.test",
            "document" => "1A2B3C4D000195",
            "type" => "cnpj",
            "pagarme_id" => "rp_xxxxxxxxxxxxxxxx",
            "status" => RecipientInterface::ACTIVE,
        ];
        $recipientFactory = new RecipientFactory();

        // Act
        $result = $recipientFactory->createFromDbData($dbData);

        // Assert
        $this->assertSame('company', $result->getType());
    }

    public function testAlphanumericCnpjFromDbDataShouldStripOnlyFormatting()
    {
        // Arrange
        $dbData = [
            "id" => 1,
            "external_id" => 2,
            "name" => "Test company",
            "email" => "test@company.test",
            "document" => "1A.2B3.C4D/0001-95",
            "type" => "cnpj",
            "pagarme_id" => "rp_xxxxxxxxxxxxxxxx",
            "status" => RecipientInterface::ACTIVE,
        ];
        $recipientFactory = new RecipientFactory();

        // Act
        $result = $recipientFactory->createFromDbData($dbData);

        // Assert
        $this->assertSame('1A2B3C4D000195', $result->getDocument());
        $this->assertSame('company', $result->getType());
    }

    public function webhookDataProvider()
    {
        return [
            "webhook with kyc_details" => [
                [
                    "id" => 'rp_xxxxxxxxxxxxxxxx',
                    "name" => "Test recipient",
                    "email" => "test@recipient.test",
                    "document" => "11111111111",
                    "description" => "Test description",
                    "type" => "individual",
                    "payment_mode" => "bank_transfer",
                    "status" => "active",
                    "kyc_details" => [
                        "status" => "approved"
                    ],
                ]
            ],
            "webhook without kyc_details" => [
                [
                    "id" => 'rp_xxxxxxxxxxxxxxxx',
                    "name" => "Test recipient",
                    "email" => "test@recipient.test",
                    "document" => "11111111111",
                    "description" => "Test description",
                    "type" => "individual",
                    "payment_mode" => "bank_transfer",
                    "status" => "active",
                ]
            ],
            "webhook with alphanumeric cnpj" => [
                [
                    "id" => 'rp_xxxxxxxxxxxxxxxx',
                    "name" => "Test company",
                    "email" => "test@company.test",
                    "document" => "1A2B3C4D000195",
                    "description" => "Test description",
                    "type" => "company",
                    "payment_mode" => "bank_transfer",
                    "status" => "active",
                    "kyc_details" => ["status" => "approved"],
                ]
            ],
        ];
    }
}
