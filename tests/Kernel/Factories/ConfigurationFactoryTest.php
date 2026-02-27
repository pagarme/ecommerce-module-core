<?php

namespace Pagarme\Core\Test\Kernel\Factories;

use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Factories\ConfigurationFactory;
use Pagarme\Core\Kernel\ValueObjects\Configuration\AddressAttributes;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\GooglePayConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\MarketplaceConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\PixConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\VoucherConfig;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Kernel\ValueObjects\Key\PublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\SecretKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestSecretKey;
use PHPUnit\Framework\TestCase;

class ConfigurationFactoryTest extends TestCase
{
    /** @var ConfigurationFactory */
    private $factory;

    /** @var array */
    private $baseData;

    public function setUp(): void
    {
        $this->factory = new ConfigurationFactory();

        $this->baseData = [
            'boletoEnabled'           => true,
            'creditCardEnabled'       => true,
            'boletoCreditCardEnabled' => false,
            'twoCreditCardsEnabled'   => false,
            'hubInstallId'            => null,
            'cardConfigs'             => [],
        ];
    }

    public function testCreateEmptyReturnsConfigurationInstance()
    {
        $config = $this->factory->createEmpty();
        $this->assertInstanceOf(Configuration::class, $config);
    }

    public function testCreateEmptyHasExpectedDefaults()
    {
        $config = $this->factory->createEmpty();
        $this->assertFalse($config->isSaveCards());
        $this->assertFalse($config->isMultiBuyer());
        $this->assertIsArray($config->getCardConfigs());
        $this->assertEmpty($config->getCardConfigs());
    }

    public function testCreateFromJsonDataWithTestPublicKeyCreatesTestPublicKeyInstance()
    {
        $data = array_merge($this->baseData, [
            'publicKey' => 'pk_test_xxxxxxxxxxxxxxxx',
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(TestPublicKey::class, $config->getPublicKey());
    }

    public function testCreateFromJsonDataWithProductionPublicKeyCreatesPublicKeyInstance()
    {
        $data = array_merge($this->baseData, [
            'publicKey' => 'pk_xxxxxxxxxxxxxxxx',
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(PublicKey::class, $config->getPublicKey());
    }

    public function testCreateFromJsonDataWithTestSecretKeyCreatesTestSecretKeyInstance()
    {
        $data = array_merge($this->baseData, [
            'secretKey' => 'sk_test_xxxxxxxxxxxxxxxx',
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(TestSecretKey::class, $config->getSecretKey());
    }

    public function testCreateFromJsonDataWithProductionSecretKeyCreatesSecretKeyInstance()
    {
        $data = array_merge($this->baseData, [
            'secretKey' => 'sk_xxxxxxxxxxxxxxxx',
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(SecretKey::class, $config->getSecretKey());
    }

    public function testCreateFromJsonDataWithHubAccessTokenCreatesHubAccessTokenKeyInstance()
    {
        $data = array_merge($this->baseData, [
            // 64 alphanumeric characters HubAccessTokenKey standard
            'secretKey' => str_repeat('a', 64),
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(HubAccessTokenKey::class, $config->getSecretKey());
    }

    public function testCreateFromJsonDataReadsPublicKeyFromLegacyKeysField()
    {
        $data = array_merge($this->baseData, [
            'keys' => [
                Configuration::KEY_PUBLIC => 'pk_test_xxxxxxxxxxxxxxxx',
                Configuration::KEY_SECRET => 'sk_test_xxxxxxxxxxxxxxxx',
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(TestPublicKey::class, $config->getPublicKey());
    }

    public function testCreateFromJsonDataReadsSecretKeyFromLegacyKeysField()
    {
        $data = array_merge($this->baseData, [
            'keys' => [
                Configuration::KEY_PUBLIC => 'pk_test_xxxxxxxxxxxxxxxx',
                Configuration::KEY_SECRET => 'sk_test_xxxxxxxxxxxxxxxx',
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(TestSecretKey::class, $config->getSecretKey());
    }

    public function testCreateFromJsonDataDoesNotOverrideExplicitPublicKeyWithLegacyKeys()
    {
        $data = array_merge($this->baseData, [
            'publicKey' => 'pk_xxxxxxxxxxxxxxxx',
            'keys'      => [
                Configuration::KEY_PUBLIC => 'pk_test_xxxxxxxxxxxxxxxx',
                Configuration::KEY_SECRET => 'sk_test_xxxxxxxxxxxxxxxx',
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        // Explicit public key (production) should prevail
        $this->assertInstanceOf(PublicKey::class, $config->getPublicKey());
    }

    public function testCreateFromJsonDataSetsAntifraudDisabledByDefault()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));

        $this->assertFalse($config->isAntifraudEnabled());
        $this->assertEquals(0, $config->getAntifraudMinAmount());
    }

    public function testCreateFromJsonDataSetsAntifraudWhenPresent()
    {
        $data = array_merge($this->baseData, [
            'antifraudEnabled'   => true,
            'antifraudMinAmount' => 5000,
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertTrue($config->isAntifraudEnabled());
        $this->assertEquals(5000, $config->getAntifraudMinAmount());
    }

    public function testCreateFromJsonDataSetsCreateOrderFalseByDefault()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));

        $this->assertFalse($config->isCreateOrderEnabled());
    }

    public function testCreateFromJsonDataSetsInstallmentsEnabledFalseByDefault()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));

        $this->assertFalse($config->isInstallmentsEnabled());
    }

    public function testCreateFromJsonDataSetsInstallmentsEnabledWhenPresent()
    {
        $data = array_merge($this->baseData, ['installmentsEnabled' => true]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertTrue($config->isInstallmentsEnabled());
    }

    public function testCreateFromJsonDataSetsBoletoEnabled()
    {
        $data = array_merge($this->baseData, ['boletoEnabled' => true]);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertTrue($config->isBoletoEnabled());
    }

    public function testCreateFromJsonDataSetsBoletoDisabled()
    {
        $data = array_merge($this->baseData, ['boletoEnabled' => false]);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertFalse($config->isBoletoEnabled());
    }

    public function testCreateFromJsonDataSetsCreditCardEnabled()
    {
        $data = array_merge($this->baseData, ['creditCardEnabled' => true]);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertTrue($config->isCreditCardEnabled());
    }

    public function testCreateFromJsonDataSetsBoletoDueDaysAsInteger()
    {
        $data = array_merge($this->baseData, ['boletoDueDays' => '7']);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertIsInt($config->getBoletoDueDays());
        $this->assertEquals(7, $config->getBoletoDueDays());
    }

    public function testCreateFromJsonDataSetsBoletoBankCode()
    {
        $data = array_merge($this->baseData, ['boletoBankCode' => '237']);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertEquals('237', $config->getBoletoBankCode());
    }

    public function testCreateFromJsonDataSetsBoletoInstructions()
    {
        $data = array_merge($this->baseData, ['boletoInstructions' => 'Pagar até o vencimento']);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertEquals('Pagar até o vencimento', $config->getBoletoInstructions());
    }

    public function testCreateFromJsonDataSetsCardStatementDescriptor()
    {
        $data = array_merge($this->baseData, ['cardStatementDescriptor' => 'Minha Loja']);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertEquals('Minha Loja', $config->getCardStatementDescriptor());
    }

    public function testCreateFromJsonDataSetsSaveCards()
    {
        $data = array_merge($this->baseData, ['saveCards' => true]);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertTrue($config->isSaveCards());
    }

    public function testCreateFromJsonDataSetsSaveVoucherCards()
    {
        $data = array_merge($this->baseData, ['saveVoucherCards' => true]);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertTrue($config->isSaveVoucherCards());
    }

    public function testCreateFromJsonDataSetsMultiBuyer()
    {
        $data = array_merge($this->baseData, ['multibuyer' => true]);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertTrue($config->isMultiBuyer());
    }

    public function testCreateFromJsonDataSetsEnabledFlag()
    {
        $data = array_merge($this->baseData, ['enabled' => true]);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertTrue($config->isEnabled());
    }

    public function testCreateFromJsonDataSetsCardOperation()
    {
        $data = array_merge($this->baseData, ['cardOperation' => Configuration::CARD_OPERATION_AUTH_ONLY]);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertEquals(Configuration::CARD_OPERATION_AUTH_ONLY, $config->getCardOperation());
    }

    public function testCreateFromJsonDataSetsHubEnvironment()
    {
        $data = array_merge($this->baseData, ['hubEnvironment' => 'Sandbox']);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertEquals('Sandbox', $config->getHubEnvironment());
    }

    public function testCreateFromJsonDataSetsAllowNoAddress()
    {
        $data = array_merge($this->baseData, ['allowNoAddress' => true]);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertTrue($config->getAllowNoAddress());
    }

    public function testCreateFromJsonDataWithNullHubInstallIdDoesNotThrow()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getHubInstallId());
    }

    public function testCreateFromJsonDataWithValidHubInstallIdCreatesGUIDInstance()
    {
        $guid = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX';
        $data = array_merge($this->baseData, ['hubInstallId' => $guid]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(GUID::class, $config->getHubInstallId());
        $this->assertEquals($guid, (string) $config->getHubInstallId());
    }

    public function testCreateFromJsonDataWithAddressAttributesCreatesCorrectObject()
    {
        $data = array_merge($this->baseData, [
            'addressAttributes' => [
                'street'       => 'street',
                'number'       => '123',
                'neighborhood' => 'neighborhood',
                'complement'   => 'complement',
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));
        $address = $config->getAddressAttributes();

        $this->assertInstanceOf(AddressAttributes::class, $address);
        $this->assertEquals('street', $address->getStreet());
        $this->assertEquals('123', $address->getNumber());
        $this->assertEquals('neighborhood', $address->getNeighborhood());
        $this->assertEquals('complement', $address->getComplement());
    }

    public function testCreateFromJsonDataWithoutAddressAttributesReturnsNull()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getAddressAttributes());
    }

    public function testCreateFromJsonDataWithPixConfigCreatesPixConfigInstance()
    {
        $data = array_merge($this->baseData, [
            'pixConfig' => [
                'enabled'           => true,
                'expirationQrCode'  => 300,
                'bankType'          => 'Pagar.me',
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(PixConfig::class, $config->getPixConfig());
        $this->assertTrue($config->getPixConfig()->isEnabled());
        $this->assertEquals(300, $config->getPixConfig()->getExpirationQrCode());
        $this->assertEquals('Pagar.me', $config->getPixConfig()->getBankType());
    }

    public function testCreateFromJsonDataWithoutPixConfigReturnsNull()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getPixConfig());
    }

    public function testCreateFromJsonDataWithGooglePayConfigCreatesGooglePayConfigInstance()
    {
        $data = array_merge($this->baseData, [
            'googlePayConfig' => [
                'enabled'      => true,
                'merchantId'   => 'merch_123',
                'merchantName' => 'Minha Loja',
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(GooglePayConfig::class, $config->getGooglePayConfig());
        $this->assertTrue($config->getGooglePayConfig()->isEnabled());
        $this->assertEquals('merch_123', $config->getGooglePayConfig()->getMerchantId());
        $this->assertEquals('Minha Loja', $config->getGooglePayConfig()->getMerchantName());
    }

    public function testCreateFromJsonDataWithoutGooglePayConfigReturnsNull()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getGooglePayConfig());
    }

    public function testCreateFromJsonDataWithRecurrenceConfigCreatesRecurrenceConfigInstance()
    {
        $data = array_merge($this->baseData, [
            'recurrenceConfig' => [
                'enabled'      => true,
                'decreaseStock' => true,
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(RecurrenceConfig::class, $config->getRecurrenceConfig());
        $this->assertTrue($config->getRecurrenceConfig()->isEnabled());
        $this->assertTrue($config->getRecurrenceConfig()->isDecreaseStock());
    }

    public function testCreateFromJsonDataWithoutRecurrenceConfigReturnsNull()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getRecurrenceConfig());
    }

    public function testCreateFromJsonDataWithVoucherConfigCreatesVoucherConfigInstance()
    {
        $data = array_merge($this->baseData, [
            'voucherConfig' => [
                'enabled'    => true,
                'title'      => 'Voucher',
                'cardConfigs' => [],
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(VoucherConfig::class, $config->getVoucherConfig());
        $this->assertTrue($config->getVoucherConfig()->isEnabled());
        $this->assertEquals('Voucher', $config->getVoucherConfig()->getTitle());
        $this->assertIsArray($config->getVoucherConfig()->getCardConfigs());
        $this->assertEmpty($config->getVoucherConfig()->getCardConfigs());
    }

    public function testCreateFromJsonDataWithoutVoucherConfigReturnsNull()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getVoucherConfig());
    }

    public function testCreateFromJsonDataWithMarketplaceConfigCreatesMarketplaceConfigInstance()
    {
        $data = array_merge($this->baseData, [
            'marketplaceConfig' => [
                'enabled'        => true,
                'mainRecipientId' => 're_xxxxxxxxxxxxxxxx',
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(MarketplaceConfig::class, $config->getMarketplaceConfig());
        $this->assertTrue($config->getMarketplaceConfig()->isEnabled());
        $this->assertEquals('re_xxxxxxxxxxxxxxxx', $config->getMarketplaceConfig()->getMainRecipientId());
    }

    public function testCreateFromJsonDataWithoutMarketplaceConfigReturnsNull()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getMarketplaceConfig());
    }

    public function testCreateFromJsonDataSetsMerchantId()
    {
        $data = array_merge($this->baseData, ['merchantId' => 'merch_123']);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertEquals('merch_123', $config->getMerchantId());
    }

    public function testCreateFromJsonDataDoesNotSetMerchantIdWhenAbsent()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getMerchantId());
    }

    public function testCreateFromJsonDataSetsAccountId()
    {
        $data = array_merge($this->baseData, ['accountId' => 'acc_123']);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertEquals('acc_123', $config->getAccountId());
    }

    public function testCreateFromJsonDataDoesNotSetAccountIdWhenAbsent()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getAccountId());
    }

    public function testCreateFromJsonDataSetsPaymentProfileId()
    {
        $data = array_merge($this->baseData, ['paymentProfileId' => 'pp_123']);
        $config = $this->factory->createFromJsonData(json_encode($data));
        $this->assertEquals('pp_123', $config->getPaymentProfileId());
    }

    public function testCreateFromJsonDataDoesNotSetPaymentProfileIdWhenAbsent()
    {
        $config = $this->factory->createFromJsonData(json_encode($this->baseData));
        $this->assertNull($config->getPaymentProfileId());
    }

    public function testCreateFromJsonDataWithValidCardConfigsAddsCardConfigs()
    {
        $data = array_merge($this->baseData, [
            'cardConfigs' => [
                [
                    'brand'                         => 'visa',
                    'enabled'                       => true,
                    'maxInstallment'                => 12,
                    'maxInstallmentWithoutInterest' => 3,
                    'initialInterest'               => 1.99,
                    'incrementalInterest'           => 0.5,
                    'minValue'                      => 10,
                ],
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertCount(1, $config->getCardConfigs());
        $this->assertInstanceOf(CardConfig::class, $config->getCardConfigs()[0]);
    }

    public function testCreateFromJsonDataWithMalformedCardConfigsDoesNotThrow()
    {
        $data = array_merge($this->baseData, [
            'cardConfigs' => [
                ['brand' => 'invalid_brand_xyz'],
            ],
        ]);

        $config = $this->factory->createFromJsonData(json_encode($data));

        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertEmpty($config->getCardConfigs());
    }

    public function testCreateFromPostDataSetsBoletoEnabled()
    {
        $postData = $this->buildPostDataWithoutCreditCard(['payment_pagarme_boleto_status' => true]);
        $config = $this->factory->createFromPostData($postData);
        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertTrue($config->isBoletoEnabled());
    }

    public function testCreateFromPostDataSetsBoletoDisabledByDefault()
    {
        $postData = $this->buildPostDataWithoutCreditCard();
        $config = $this->factory->createFromPostData($postData);
        $this->assertFalse($config->isBoletoEnabled());
    }

    public function testCreateFromPostDataSetsCreditCardEnabled()
    {
        $postData = $this->buildPostDataWithoutCreditCard(['payment_pagarme_credit_card_status' => true]);
        $config = $this->factory->createFromPostData($postData);
        $this->assertTrue($config->isCreditCardEnabled());
    }

    public function testCreateFromPostDataSetsBoletoCreditCardEnabled()
    {
        $postData = $this->buildPostDataWithoutCreditCard(['payment_pagarme_boletoCreditCard_status' => true]);
        $config = $this->factory->createFromPostData($postData);
        $this->assertTrue($config->isBoletoCreditCardEnabled());
    }

    public function testCreateFromPostDataSetsTwoCreditCardsEnabled()
    {
        $postData = $this->buildPostDataWithoutCreditCard([
            'payment_pagarme_credit_card_two_credit_cards_enabled' => true,
        ]);
        $config = $this->factory->createFromPostData($postData);
        $this->assertTrue($config->isTwoCreditCardsEnabled());
    }

    public function testCreateFromPostDataSetsStoreId()
    {
        $postData = $this->buildPostDataWithoutCreditCard(['payment_pagarme_store_id' => 'store_42']);
        $config = $this->factory->createFromPostData($postData);
        $this->assertEquals('store_42', $config->getStoreId());
    }

    public function testCreateFromPostDataWithValidBrandCreatesCardConfig()
    {
        $postData = $this->buildPostDataWithCreditCards();
        $config = $this->factory->createFromPostData($postData);

        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertCount(1, $config->getCardConfigs());
        $this->assertInstanceOf(CardConfig::class, $config->getCardConfigs()[0]);
    }

    public function testCreateFromPostDataWithValidBrandSetsCorrectInstallments()
    {
        $postData = $this->buildPostDataWithCreditCards();
        $config = $this->factory->createFromPostData($postData);

        $cardConfig = $config->getCardConfigs()[0];
        $this->assertEquals(12, $cardConfig->getMaxInstallment());
        $this->assertEquals(3, $cardConfig->getMaxInstallmentWithoutInterest());
        $this->assertEquals(1.99, $cardConfig->getInitialInterest());
        $this->assertEquals(0.5, $cardConfig->getIncrementalInterest());
    }

    public function testCreateFromPostDataWithInvalidBrandSilentlySkipsCardConfig()
    {
        $postData = $this->buildPostDataWithoutCreditCard([
            'creditCard' => [
                'InvalidBrandXyz' => [
                    'is_enabled'                    => true,
                    'installments_up_to'            => 6,
                    'installments_without_interest' => 1,
                    'interest'                      => 0.0,
                    'incremental_interest'          => 0.0,
                ],
            ],
        ]);

        $config = $this->factory->createFromPostData($postData);

        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertEmpty($config->getCardConfigs());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildPostDataWithoutCreditCard(array $overrides = []): array
    {
        return array_merge([
            'payment_pagarme_boleto_status'                        => false,
            'payment_pagarme_credit_card_status'                   => false,
            'payment_pagarme_boletoCreditCard_status'              => false,
            'payment_pagarme_credit_card_two_credit_cards_enabled' => false,
            'payment_pagarme_store_id'                             => 'store_123',
            'creditCard'                                           => [],
        ], $overrides);
    }

    private function buildPostDataWithCreditCards(array $overrides = []): array
    {
        return array_merge([
            'payment_pagarme_boleto_status'                        => false,
            'payment_pagarme_credit_card_status'                   => false,
            'payment_pagarme_boletoCreditCard_status'              => false,
            'payment_pagarme_credit_card_two_credit_cards_enabled' => false,
            'payment_pagarme_store_id'                             => 'store_123',
            'creditCard'                                           => [
                'Visa' => [
                    'is_enabled'                    => true,
                    'installments_up_to'            => 12,
                    'installments_without_interest' => 3,
                    'interest'                      => 1.99,
                    'incremental_interest'          => 0.5,
                ],
            ],
        ], $overrides);
    }
}
