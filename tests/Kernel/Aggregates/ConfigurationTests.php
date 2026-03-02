<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\PoiType;
use Pagarme\Core\Kernel\ValueObjects\Key\PublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestSecretKey;
use PHPUnit\Framework\TestCase;

class ConfigurationTests extends TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function setUp(): void
    {
        $this->configuration = new Configuration();
    }

    public function testIsEnabled()
    {
        $this->configuration->setEnabled(true);
        $this->assertIsBool($this->configuration->isEnabled());
        $this->assertTrue($this->configuration->isEnabled());
    }

    public function testIsUnabled()
    {
        $this->configuration->setEnabled(false);
        $this->assertIsBool($this->configuration->isEnabled());
        $this->assertFalse($this->configuration->isEnabled());
    }

    public function testHubEnvironmentStartsNull()
    {
        $this->assertNull($this->configuration->getHubEnvironment());
        $this->assertEquals('', $this->configuration->getHubEnvironment());
    }

    public function testHubEnvironmentIsSandbox()
    {
        $this->configuration->setHubEnvironment('Sandbox');
        $this->assertIsString($this->configuration->getHubEnvironment());
        $this->assertEquals('Sandbox', $this->configuration->getHubEnvironment());
    }

    public function testHubEnvironmentIsProduction()
    {
        $this->configuration->setHubEnvironment('Production');
        $this->assertIsString($this->configuration->getHubEnvironment());
        $this->assertEquals('Production', $this->configuration->getHubEnvironment());
    }

    public function testDefaultValuesOnConstruction()
    {
        $this->assertFalse($this->configuration->isSaveCards());
        $this->assertFalse($this->configuration->isSaveVoucherCards());
        $this->assertFalse($this->configuration->isMultiBuyer());
        $this->assertFalse($this->configuration->isInheritedAll());
        $this->assertFalse($this->configuration->isInstallmentsDefaultConfig());
        $this->assertIsArray($this->configuration->getCardConfigs());
        $this->assertEmpty($this->configuration->getCardConfigs());
    }

    public function testBoletoEnabledSetTrue()
    {
        $this->configuration->setBoletoEnabled(true);
        $this->assertIsBool($this->configuration->isBoletoEnabled());
        $this->assertTrue($this->configuration->isBoletoEnabled());
    }

    public function testBoletoEnabledSetFalse()
    {
        $this->configuration->setBoletoEnabled(false);
        $this->assertIsBool($this->configuration->isBoletoEnabled());
        $this->assertFalse($this->configuration->isBoletoEnabled());
    }

    public function testBoletoDueDaysWithValidNumericValue()
    {
        $this->configuration->setBoletoDueDays(3);
        $this->assertIsInt($this->configuration->getBoletoDueDays());
        $this->assertEquals(3, $this->configuration->getBoletoDueDays());
    }

    public function testBoletoDueDaysWithStringNumericValue()
    {
        $this->configuration->setBoletoDueDays('5');
        $this->assertIsInt($this->configuration->getBoletoDueDays());
        $this->assertEquals(5, $this->configuration->getBoletoDueDays());
    }

    public function testBoletoDueDaysWithInvalidValueThrowsException()
    {
        $this->expectException(InvalidParamException::class);
        $this->configuration->setBoletoDueDays('abc');
    }

    public function testBoletoBankCodeSetAndGet()
    {
        $this->configuration->setBoletoBankCode('001');
        $this->assertIsString($this->configuration->getBoletoBankCode());
        $this->assertEquals('001', $this->configuration->getBoletoBankCode());
    }

    public function testBoletoInstructionsSetAndGet()
    {
        $instructions = 'Pagar até o vencimento';
        $this->configuration->setBoletoInstructions($instructions);
        $this->assertEquals($instructions, $this->configuration->getBoletoInstructions());
    }

    public function testCreditCardEnabledSetTrue()
    {
        $this->configuration->setCreditCardEnabled(true);
        $this->assertIsBool($this->configuration->isCreditCardEnabled());
        $this->assertTrue($this->configuration->isCreditCardEnabled());
    }

    public function testCreditCardEnabledSetFalse()
    {
        $this->configuration->setCreditCardEnabled(false);
        $this->assertFalse($this->configuration->isCreditCardEnabled());
    }

    public function testCardOperationAuthOnly()
    {
        $this->configuration->setCardOperation(Configuration::CARD_OPERATION_AUTH_ONLY);
        $this->assertEquals(Configuration::CARD_OPERATION_AUTH_ONLY, $this->configuration->getCardOperation());
        $this->assertFalse($this->configuration->isCapture());
    }

    public function testCardOperationAuthAndCapture()
    {
        $this->configuration->setCardOperation(Configuration::CARD_OPERATION_AUTH_AND_CAPTURE);
        $this->assertEquals(Configuration::CARD_OPERATION_AUTH_AND_CAPTURE, $this->configuration->getCardOperation());
        $this->assertTrue($this->configuration->isCapture());
    }

    public function testCardStatementDescriptorWithValidValue()
    {
        $this->configuration->setCardStatementDescriptor('Minha Loja');
        $this->assertEquals('Minha Loja', $this->configuration->getCardStatementDescriptor());
    }

    public function testCardStatementDescriptorRemovesSpecialCharacters()
    {
        $this->configuration->setCardStatementDescriptor('Loja@#!');
        $result = $this->configuration->getCardStatementDescriptor();
        $this->assertStringNotContainsString('@', $result);
        $this->assertStringNotContainsString('#', $result);
        $this->assertStringNotContainsString('!', $result);
    }

    public function testCardStatementDescriptorTooLongThrowsException()
    {
        $this->expectException(InvalidParamException::class);
        $this->configuration->setCardStatementDescriptor('DescricaoLongaDeMaisParaOLimite');
    }

    public function testAntifraudEnabledSetAndGet()
    {
        $this->configuration->setAntifraudEnabled(true);
        $this->assertTrue($this->configuration->isAntifraudEnabled());
    }

    public function testAntifraudDisabledSetAndGet()
    {
        $this->configuration->setAntifraudEnabled(false);
        $this->assertFalse($this->configuration->isAntifraudEnabled());
    }

    public function testAntifraudMinAmountStripsNonNumericCharacters()
    {
        $this->configuration->setAntifraudMinAmount('R$ 150,00');
        $this->assertEquals('15000', $this->configuration->getAntifraudMinAmount());
    }

    public function testAntifraudMinAmountWithValidInteger()
    {
        $this->configuration->setAntifraudMinAmount(500);
        $this->assertEquals('500', $this->configuration->getAntifraudMinAmount());
    }

    public function testAntifraudMinAmountNegativeBecomesZero()
    {
        $this->configuration->setAntifraudMinAmount(-100);
        $this->assertEquals('0', $this->configuration->getAntifraudMinAmount());
    }

    public function testIsHubEnabledReturnsFalseWhenNoInstallId()
    {
        $this->assertFalse($this->configuration->isHubEnabled());
    }

    public function testIsHubEnabledReturnsFalseForZeroGuid()
    {
        $guid = new GUID('00000000-0000-0000-0000-000000000000');
        $this->configuration->setHubInstallId($guid);
        $this->assertFalse($this->configuration->isHubEnabled());
    }

    public function testIsHubEnabledReturnsTrueForValidGuid()
    {
        $guid = new GUID('12345678-1234-1234-1234-123456789012');
        $this->configuration->setHubInstallId($guid);
        $this->assertTrue($this->configuration->isHubEnabled());
    }

    public function testGetHubInstallIdReturnsSetGuid()
    {
        $guid = new GUID('12345678-1234-1234-1234-123456789012');
        $this->configuration->setHubInstallId($guid);
        $this->assertInstanceOf(GUID::class, $this->configuration->getHubInstallId());
        $this->assertEquals(
            '12345678-1234-1234-1234-123456789012',
            $this->configuration->getHubInstallId()->getValue()
        );
    }

    public function testGetParentIdReturnsNullWhenNoParent()
    {
        $this->assertNull($this->configuration->getParentId());
    }

    public function testGetMethodsInheritedReturnsEmptyArrayWhenNoParent()
    {
        $this->assertIsArray($this->configuration->getMethodsInherited());
        $this->assertEmpty($this->configuration->getMethodsInherited());
    }

    public function testIsInheritedAllReturnsFalseWhenNoParent()
    {
        $this->assertFalse($this->configuration->isInheritedAll());
    }

    public function testIsInheritedAllReturnsFalseWithParentButFlagNotSet()
    {
        $parent = new Configuration();
        $this->configuration->setParentConfiguration($parent);
        $this->assertFalse($this->configuration->isInheritedAll());
    }

    public function testIsInheritedAllReturnsTrueWithParentAndFlagSet()
    {
        $parent = new Configuration();
        $this->configuration->setParentConfiguration($parent);
        $this->configuration->setInheritAll(true);
        $this->assertTrue($this->configuration->isInheritedAll());
    }

    public function testGetMethodsInheritedWithParentSet()
    {
        $parent = new Configuration();
        $methods = ['isBoletoEnabled', 'isCreditCardEnabled'];
        $this->configuration->setParentConfiguration($parent);
        $this->configuration->setMethodsInherited($methods);
        $this->assertEquals($methods, $this->configuration->getMethodsInherited());
    }

    public function testSaveCardsDefaultIsFalse()
    {
        $this->assertFalse($this->configuration->isSaveCards());
    }

    public function testSaveCardsSetTrue()
    {
        $this->configuration->setSaveCards(true);
        $this->assertTrue($this->configuration->isSaveCards());
    }

    public function testSaveVoucherCardsSetAndGet()
    {
        $this->configuration->setSaveVoucherCards(true);
        $this->assertTrue($this->configuration->isSaveVoucherCards());

        $this->configuration->setSaveVoucherCards(false);
        $this->assertFalse($this->configuration->isSaveVoucherCards());
    }

    public function testMultiBuyerSetAndGet()
    {
        $this->configuration->setMultiBuyer(true);
        $this->assertTrue($this->configuration->isMultiBuyer());

        $this->configuration->setMultiBuyer(false);
        $this->assertFalse($this->configuration->isMultiBuyer());
    }

    public function testStoreIdSetAndGet()
    {
        $this->configuration->setStoreId('store_123');
        $this->assertEquals('store_123', $this->configuration->getStoreId());
    }

    public function testMerchantIdSetAndGet()
    {
        $this->configuration->setMerchantId('merch_123');
        $this->assertEquals('merch_123', $this->configuration->getMerchantId());
    }

    public function testAccountIdSetAndGet()
    {
        $this->configuration->setAccountId('acc_123');
        $this->assertEquals('acc_123', $this->configuration->getAccountId());
    }

    public function testAccountIdAcceptsNull()
    {
        $this->configuration->setAccountId(null);
        $this->assertNull($this->configuration->getAccountId());
    }

    public function testPaymentProfileIdSetAndGet()
    {
        $this->configuration->setPaymentProfileId('pp_123');
        $this->assertEquals('pp_123', $this->configuration->getPaymentProfileId());
    }

    public function testPaymentProfileIdAcceptsNull()
    {
        $this->configuration->setPaymentProfileId(null);
        $this->assertNull($this->configuration->getPaymentProfileId());
    }

    public function testPoiTypeDefaultsToEmptyArray()
    {
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEmpty($this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithNullReturnsEmptyArray()
    {
        $this->configuration->setPoiType(null);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEmpty($this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithEmptyArrayReturnsEmptyArray()
    {
        $this->configuration->setPoiType([]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEmpty($this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithPos()
    {
        $this->configuration->setPoiType([PoiType::POS]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEquals([PoiType::POS], $this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithTef()
    {
        $this->configuration->setPoiType([PoiType::TEF]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEquals([PoiType::TEF], $this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithLink()
    {
        $this->configuration->setPoiType([PoiType::LINK]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEquals([PoiType::LINK], $this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithTapOnPhone()
    {
        $this->configuration->setPoiType([PoiType::TAP_ON_PHONE]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEquals([PoiType::TAP_ON_PHONE], $this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithWhatsappPay()
    {
        $this->configuration->setPoiType([PoiType::WHATSAPP_PAY]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEquals([PoiType::WHATSAPP_PAY], $this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithEcommerce()
    {
        $this->configuration->setPoiType([PoiType::ECOMMERCE]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEquals([PoiType::ECOMMERCE], $this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithMicroPos()
    {
        $this->configuration->setPoiType([PoiType::MICRO_POS]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEquals([PoiType::MICRO_POS], $this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithManualEntry()
    {
        $this->configuration->setPoiType([PoiType::MANUAL_ENTRY]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEquals([PoiType::MANUAL_ENTRY], $this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithInvalidValueIsReplacedByDefault()
    {
        $this->configuration->setPoiType(['InvalidType']);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEquals([PoiType::DEFAULT], $this->configuration->getPoiType());
    }

    public function testSetPoiTypeWithMultipleValidValues()
    {
        $this->configuration->setPoiType([PoiType::POS, PoiType::TEF, PoiType::ECOMMERCE]);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertCount(3, $this->configuration->getPoiType());
        $this->assertContains(PoiType::POS, $this->configuration->getPoiType());
        $this->assertContains(PoiType::TEF, $this->configuration->getPoiType());
        $this->assertContains(PoiType::ECOMMERCE, $this->configuration->getPoiType());
    }

    public function testSetPoiTypeDeduplicatesDuplicateValues()
    {
        $this->configuration->setPoiType([PoiType::POS, PoiType::POS, PoiType::TEF]);
        $result = $this->configuration->getPoiType();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContains(PoiType::POS, $result);
        $this->assertContains(PoiType::TEF, $result);
    }

    public function testSetPoiTypeWithMixedValidAndInvalidValues()
    {
        $this->configuration->setPoiType([PoiType::POS, 'InvalidType']);
        $result = $this->configuration->getPoiType();
        $this->assertIsArray($result);
        $this->assertContains(PoiType::POS, $result);
        $this->assertContains(PoiType::DEFAULT, $result);
    }

    public function testSetPoiTypeWithNullReplacesExistingValueWithEmptyArray()
    {
        $this->configuration->setPoiType([PoiType::POS]);
        $this->assertEquals([PoiType::POS], $this->configuration->getPoiType());

        $this->configuration->setPoiType(null);
        $this->assertIsArray($this->configuration->getPoiType());
        $this->assertEmpty($this->configuration->getPoiType());
    }

    public function testSetPoiTypeReplacesExistingValueWithAnotherValidType()
    {
        $this->configuration->setPoiType([PoiType::POS]);
        $this->configuration->setPoiType([PoiType::ECOMMERCE]);
        $this->assertEquals([PoiType::ECOMMERCE], $this->configuration->getPoiType());
    }

    public function testAddCardConfigSuccessfully()
    {
        $cardConfig = new CardConfig(true, CardBrand::visa(), 12, 6, 1.99, 0.50, 500);
        $this->configuration->addCardConfig($cardConfig);

        $configs = $this->configuration->getCardConfigs();
        $this->assertCount(1, $configs);
        $this->assertSame($cardConfig, $configs[0]);
    }

    public function testAddMultipleDistinctCardConfigs()
    {
        $visaConfig = new CardConfig(true, CardBrand::visa(), 12, 6, 1.99, 0.50, 500);
        $masterConfig = new CardConfig(true, CardBrand::mastercard(), 12, 6, 1.99, 0.50, 500);

        $this->configuration->addCardConfig($visaConfig);
        $this->configuration->addCardConfig($masterConfig);

        $this->assertCount(2, $this->configuration->getCardConfigs());
        $this->assertSame([$visaConfig, $masterConfig], $this->configuration->getCardConfigs());
    }

    public function testAddDuplicateCardConfigThrowsException()
    {
        $this->expectException(InvalidParamException::class);

        $brand           = CardBrand::visa();
        $cardConfig      = new CardConfig(true, $brand, 12, 6, 1.99, 0.50, 500);
        $duplicateConfig = new CardConfig(true, $brand, 12, 6, 1.99, 0.50, 500);

        $this->configuration->addCardConfig($cardConfig);
        $this->configuration->addCardConfig($duplicateConfig);
    }

    public function testDefaultTestModeIsTrue()
    {
        $this->assertTrue($this->configuration->isTestMode());
    }

    public function testSetTestPublicKeySetsTestModeTrue()
    {
        $key = new TestPublicKey('pk_test_1234567890123456');
        $this->configuration->setPublicKey($key);
        $this->assertTrue($this->configuration->isTestMode());
    }

    public function testSetProductionPublicKeySetsTestModeFalse()
    {
        $key = new PublicKey('pk_1234567890123456');
        $this->configuration->setPublicKey($key);
        $this->assertFalse($this->configuration->isTestMode());
    }

    public function testSetSecretKeyStoresKey()
    {
        $key = new TestSecretKey('sk_test_1234567890123456');
        $this->configuration->setSecretKey($key);
        $this->assertInstanceOf(TestSecretKey::class, $this->configuration->getSecretKey());
    }

    public function testJsonSerializeContainsAllExpectedKeys()
    {
        $serialized = $this->configuration->jsonSerialize();

        $expectedKeys = [
            'enabled', 'antifraudEnabled', 'antifraudMinAmount', 'boletoEnabled',
            'creditCardEnabled', 'saveCards', 'saveVoucherCards', 'multiBuyer',
            'twoCreditCardsEnabled', 'boletoCreditCardEnabled', 'testMode',
            'hubInstallId', 'hubEnvironment', 'merchantId', 'accountId',
            'paymentProfileId', 'poiType', 'addressAttributes', 'allowNoAddress', 'keys',
            'cardOperation', 'installmentsEnabled', 'installmentsDefaultConfig',
            'cardStatementDescriptor', 'boletoInstructions', 'boletoDueDays',
            'boletoBankCode', 'cardConfigs', 'storeId', 'methodsInherited',
            'parentId', 'parent', 'inheritAll', 'recurrenceConfig',
            'sendMail', 'createOrder', 'voucherConfig', 'debitConfig',
            'pixConfig', 'googlePayConfig', 'marketplaceConfig',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $serialized, "Key '{$key}' is missing from jsonSerialize output.");
        }
    }

    public function testJsonSerializeReflectsSetValues()
    {
        $this->configuration->setEnabled(true);
        $this->configuration->setStoreId('store_123');
        $this->configuration->setBoletoBankCode('001');

        $serialized = $this->configuration->jsonSerialize();

        $this->assertTrue($serialized['enabled']);
        $this->assertEquals('store_123', $serialized['storeId']);
        $this->assertEquals('001', $serialized['boletoBankCode']);
    }

    public function testJsonSerializePoiTypeIsEmptyArrayByDefault()
    {
        $serialized = $this->configuration->jsonSerialize();
        $this->assertIsArray($serialized['poiType']);
        $this->assertEmpty($serialized['poiType']);
    }

    public function testJsonSerializeReflectsPoiTypeValue()
    {
        $this->configuration->setPoiType([PoiType::ECOMMERCE]);
        $serialized = $this->configuration->jsonSerialize();
        $this->assertIsArray($serialized['poiType']);
        $this->assertEquals([PoiType::ECOMMERCE], $serialized['poiType']);
    }
}
