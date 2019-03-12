<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Aggregates\Configuration;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Mundipagg\Core\Kernel\ValueObjects\Id\GUID;
use Mundipagg\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\PublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\SecretKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\TestSecretKey;

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
        $isAntifraudEnabled = false;
        if (isset($data->isAntifraudEnabled)) {
            $isAntifraudEnabled = $data->isAntifraudEnabled;
        }
        $config->setAntifraudEnabled($isAntifraudEnabled);
        $config->setBoletoEnabled($data->boletoEnabled);
        $config->setCreditCardEnabled($data->creditCardEnabled);
        $config->setBoletoCreditCardEnabled($data->boletoCreditCardEnabled);
        $config->setTwoCreditCardsEnabled($data->twoCreditCardsEnabled);

        if (isset($data->enabled)) {
            $config->setEnabled($data->enabled);
        }

        if (isset($data->cardOperation)) {
            $config->setCardOperation($data->cardOperation);
        }

        if ($data->hubInstallId !== null) {
            $config->setHubInstallId(
                new GUID($data->hubInstallId)
            );
        }

        if (isset($data->keys) ) {
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

        return $config;
    }


    private function createPublicKey($key)
    {
        try {
            return new TestPublicKey($key);
        } catch(\Throwable $e) {
        }

        return new PublicKey($key);
    }

    private function createSecretKey($key)
    {
        try {
            return new TestSecretKey($key);
        } catch(\Throwable $e) {
        }

        try {
            return new SecretKey($key);
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