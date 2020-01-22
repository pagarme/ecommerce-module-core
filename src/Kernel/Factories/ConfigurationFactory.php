<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Aggregates\Configuration;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\Repositories\ConfigurationRepository;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\AddressAttributes;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;
use Mundipagg\Core\Kernel\ValueObjects\Id\GUID;
use Mundipagg\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\PublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\SecretKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\TestSecretKey;
use Exception;

class ConfigurationFactory implements FactoryInterface
{
    public function createEmpty()
    {
        return new Configuration();
    }

    public function createFromPostData($postData)
    {
        $config = new Configuration();

        foreach ($postData['creditCard'] as $brand => $cardConfig) {
            $config->addCardConfig(
                new CardConfig(
                    $cardConfig['is_enabled'],
                    $brand,
                    $cardConfig['installments_up_to'],
                    $cardConfig['installments_without_interest'],
                    $cardConfig['interest'],
                    $cardConfig['incremental_interest']
                )
            );
        }

        $config->setBoletoEnabled($postData['payment_mundipagg_boleto_status']);
        $config->setCreditCardEnabled($postData['payment_mundipagg_credit_card_status']);
        $config->setBoletoCreditCardEnabled($postData['payment_mundipagg_boletoCreditCard_status']);
        $config->setTwoCreditCardsEnabled($postData['payment_mundipagg_credit_card_two_credit_cards_enabled']);

        $config->setStoreId($postData['payment_mundipagg_store_id']);

        return $config;
    }

    public function createFromJsonData($json)
    {
        $config = new Configuration();
        $data = json_decode($json);

        foreach ($data->cardConfigs as $cardConfig) {
            $brand = strtolower($cardConfig->brand);
            $config->addCardConfig(
                new CardConfig(
                    $cardConfig->enabled,
                    CardBrand::$brand(),
                    $cardConfig->maxInstallment,
                    $cardConfig->maxInstallmentWithoutInterest,
                    $cardConfig->initialInterest,
                    $cardConfig->incrementalInterest,
                    $cardConfig->minValue
                )
            );
        }
        $antifraudEnabled = false;
        $antifraudMinAmount = 0;

        if (!empty($data->antifraudEnabled)) {
            $antifraudEnabled = $data->antifraudEnabled;
            $antifraudMinAmount = $data->antifraudMinAmount;
        }

        $config->setAntifraudEnabled($antifraudEnabled);
        $config->setAntifraudMinAmount($antifraudMinAmount);
        $config->setBoletoEnabled($data->boletoEnabled);
        $config->setCreditCardEnabled($data->creditCardEnabled);
        $config->setBoletoCreditCardEnabled($data->boletoCreditCardEnabled);
        $config->setTwoCreditCardsEnabled($data->twoCreditCardsEnabled);

        if (!empty($data->sendMail)) {
            $config->setSendMailEnabled($data->sendMail);
        }

        if (!empty($data->methodsInherited)) {
            $config->setMethodsInherited($data->methodsInherited);
        }

        if (!empty($data->inheritAll)) {
            $config->setInheritAll($data->inheritAll);
        }

        if (!empty($data->storeId) && $data->storeId !== null) {
            $config->setStoreId($data->storeId);
        }

        if (!empty($data->parentId)) {
            $configurationRepository = new ConfigurationRepository();
            $configDefault = $configurationRepository->find($data->parentId);
            $config->setParentConfiguration($configDefault);
        }

        $isInstallmentsEnabled = false;
        if (!empty($data->installmentsEnabled)) {
            $isInstallmentsEnabled = $data->installmentsEnabled;
        }
        $config->setInstallmentsEnabled($isInstallmentsEnabled);

        if (!empty($data->enabled)) {
            $config->setEnabled($data->enabled);
        }

        if (!empty($data->cardOperation)) {
            $config->setCardOperation($data->cardOperation);
        }

        if ($data->hubInstallId !== null) {
            $config->setHubInstallId(
                new GUID($data->hubInstallId)
            );
        }

        if (!empty($data->keys) ) {
            if (!isset($data->publicKey)) {
                $index = Configuration::KEY_PUBLIC;
                $data->publicKey = $data->keys->$index;
            }

            if (!isset($data->secretKey)) {
                $index = Configuration::KEY_SECRET;
                $data->secretKey = $data->keys->$index;
            }
        }

        if (!empty($data->publicKey)) {
            $config->setPublicKey(
                $this->createPublicKey($data->publicKey)
            );
        }

        if (!empty($data->secretKey)) {
            $config->setSecretKey(
                $this->createSecretKey($data->secretKey)
            );
        }

        if (!empty($data->addressAttributes)) {
            $config->setAddressAttributes(
                new AddressAttributes(
                    $data->addressAttributes->street,
                    $data->addressAttributes->number,
                    $data->addressAttributes->neighborhood,
                    $data->addressAttributes->complement
                )
            );
        }

        if (!empty($data->cardStatementDescriptor)) {
            $config->setCardStatementDescriptor($data->cardStatementDescriptor);
        }

        if (!empty($data->boletoInstructions)) {
            $config->setBoletoInstructions($data->boletoInstructions);
        }

        if (!empty($data->boletoBankCode)) {
            $config->setBoletoBankCode($data->boletoBankCode);
        }
        if (!empty($data->boletoDueDays)) {
            $config->setBoletoDueDays($data->boletoDueDays);
        }

        if (!empty($data->saveCards)) {
            $config->setSaveCards($data->saveCards);
        }

        if (!empty($data->multibuyer)) {
            $config->setMultiBuyer($data->multibuyer);
        }

        if (!empty($data->recurrenceConfig)) {
            $config->setRecurrenceConfig(
                (new RecurrenceConfigFactory())
                    ->createFromDbData($data->recurrenceConfig)
            );
        }

        if (isset($data->installmentsDefaultConfig)) {
            $config->setInstallmentsDefaultConfig(
                $data->installmentsDefaultConfig
            );
        }

        return $config;
    }


    private function createPublicKey($key)
    {
        try {
            return new TestPublicKey($key);
        } catch(\Exception $e) {

        } catch(\Throwable $e) {

        }

        return new PublicKey($key);
    }

    private function createSecretKey($key)
    {
        try {
            return new TestSecretKey($key);
        } catch(\Exception $e) {

        } catch(\Throwable $e) {

        }

        try {
            return new SecretKey($key);
        } catch(\Exception $e) {

        } catch(\Throwable $e) {

        }

        return new HubAccessTokenKey($key);
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }
}
