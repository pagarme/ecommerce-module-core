<?php

namespace Mundipagg\Core\Kernel\Aggregates;

use Exception;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Mundipagg\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\PublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\SecretKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\TestSecretKey;
use Mundipagg\Core\Kernel\ValueObjects\Id\GUID;

final class Configuration extends AbstractEntity
{
    const CREDIT_CARD_BRAND_DEFAULT = 'Default';
    const CREDIT_CARD_BRAND_VISA = 'Visa';
    const CREDIT_CARD_BRAND_MASTERCARD = 'Mastercard';
    const CREDIT_CARD_BRAND_HIPERCARD = 'Hipercard';
    const CREDIT_CARD_BRAND_ELO = 'Elo';
    const CREDIT_CARD_BRAND_DINERS = 'Diners';
    const CREDIT_CARD_BRAND_AMEX = 'Amex';

    const BOLETO_BRAND_BOLETO = 'Boleto';

    static private $validBrands = [
        self::CREDIT_CARD_BRAND_DEFAULT,
        self::CREDIT_CARD_BRAND_VISA,
        self::CREDIT_CARD_BRAND_MASTERCARD,
        self::CREDIT_CARD_BRAND_HIPERCARD,
        self::CREDIT_CARD_BRAND_ELO,
        self::CREDIT_CARD_BRAND_DINERS,
        self::CREDIT_CARD_BRAND_AMEX
    ];

    /**
     *
     * @fixme since the module environment is defined by the type of public key
     * there is no point in saving the test keys.
     */
    const KEY_SECRET = 'KEY_SECRET';
    const KEY_PUBLIC = 'KEY_PUBLIC';

    /**
     *
     * @var bool 
     */
    private $disabled;
    /**
     *
     * @var bool 
     */
    private $boletoEnabled;
    /**
     *
     * @var bool 
     */
    private $creditCardEnabled;
    /**
     *
     * @var bool 
     */
    private $twoCreditCardsEnabled;
    /**
     *
     * @var bool 
     */
    private $boletoCreditCardEnabled;
    /**
     *
     * @var bool 
     */
    private $testMode;
    /**
     *
     * @var GUID 
     */
    private $hubInstallId;

    /**
     *
     * @var AbstractValidString[]
     */
    private $keys;

    /**
     *
     * @var CardConfig[]
     */
    private $cardConfigs;

    public function __construct()
    {
        $this->disabled = false;
        $this->cardConfigs = [];

        $this->keys = [
            self::KEY_SECRET => null,
            self::KEY_PUBLIC => null,
        ];

        $this->hubInstallId = new HubAccessTokenKey(null);
    }

    public function isDisabled()
    {
        return $this->disabled;
    }

    public function setDisabled($disabled)
    {
        $this->disabled = filter_var(
            $disabled,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function getId()
    {
        return 0;
    }

    public function getPublicKey()
    {
        return $this->keys[self::KEY_PUBLIC];
    }

    public function getSecretKey()
    {
        return $this->keys[self::KEY_SECRET];
    }

    /**
     *
     * @param  string|array $key
     * @return $this
     */
    public function setPublicKey($key)
    {
        $index = self::KEY_PUBLIC;
        $keyClass = PublicKey::class;
        if ($this->isTestMode()) {
            $keyClass = TestPublicKey::class;
        }

        if (is_array($key)) {
            $key = $key[$index];
        }

        $this->keys[$index] = new $keyClass($key);
        return $this;
    }

    /**
     *
     * @param  string|array $key
     * @return $this
     */
    public function setSecretKey($key)
    {
        $index = self::KEY_SECRET;
        $keyClass = SecretKey::class;
        if ($this->isTestMode()) {
            $keyClass = TestSecretKey::class;
        }

        if ($this->isHubEnabled()) {
            $keyClass = HubAccessTokenKey::class;
        }

        if (is_array($key)) {
            $key = $key[$index];
        }

        $this->keys[$index] = new $keyClass($key);
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isTestMode()
    {
        return $this->testMode;
    }

    /**
     *
     * @deprecated Since the test mode is defined by the
     * presence of an test public key, this is not necessary.
     *
     * @param  bool $testMode
     * @return Configuration
     */
    public function setTestMode($testMode)
    {
        $this->testMode = filter_var(
            $testMode,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isHubEnabled()
    {
        return $this->hubInstallId->getValue() !== null;
    }

    public function setHubInstallId(GUID $hubInstallId)
    {
        $this->hubInstallId = $hubInstallId;
    }

    public function getHubInstallId()
    {
        return $this->hubInstallId;
    }

    /**
     *
     * @param  bool $boletoEnabled
     * @return Configuration
     */
    public function setBoletoEnabled($boletoEnabled)
    {
        $this->boletoEnabled = filter_var(
            $boletoEnabled,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     *
     * @param  bool $creditCardEnabled
     * @return Configuration
     */
    public function setCreditCardEnabled($creditCardEnabled)
    {
        $this->creditCardEnabled = filter_var(
            $creditCardEnabled,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     *
     * @param  bool $twoCreditCardsEnabled
     * @return Configuration
     */
    public function setTwoCreditCardsEnabled($twoCreditCardsEnabled)
    {
        $this->twoCreditCardsEnabled = filter_var(
            $twoCreditCardsEnabled,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     *
     * @param  bool $boletoCreditCardEnabled
     * @return Configuration
     */
    public function setBoletoCreditCardEnabled($boletoCreditCardEnabled)
    {
        $this->boletoCreditCardEnabled = filter_var(
            $boletoCreditCardEnabled,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isBoletoEnabled()
    {
        return $this->boletoEnabled;
    }

    /**
     *
     * @return bool
     */
    public function isCreditCardEnabled()
    {
        return $this->creditCardEnabled;
    }

    /**
     *
     * @return bool
     */
    public function isTwoCreditCardsEnabled()
    {
        return $this->twoCreditCardsEnabled;
    }

    /**
     *
     * @return bool
     */
    public function isBoletoCreditCardEnabled()
    {
        return $this->boletoCreditCardEnabled;
    }

    /**
     *
     * @param  CardConfig $installmentConfig
     * @throws Exception
     */
    public function addCardConfig(CardConfig $newCardConfig)
    {
        if (!in_array($newCardConfig->getBrand(), self::$validBrands)) {
            throw new InvalidParamException(
                "The brand is invalid!",
                $newCardConfig->getBrand()
            );
        }

        foreach ($this->cardConfigs as $cardConfig) {
            if ($cardConfig->equals($newCardConfig)) {
                throw new InvalidParamException(
                    "The card config is already added!",
                    $newCardConfig->getBrand()
                );
            }
        }

        $this->cardConfigs[] = $newCardConfig;
    }

    /**
     *
     * @return CardConfig[]
     */
    public function getCardConfigs()
    {
        return $this->cardConfigs;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "disabled" => $this->disabled,
            "boletoEnabled" => $this->boletoEnabled,
            "creditCardEnabled" => $this->creditCardEnabled,
            "twoCreditCardsEnabled" => $this->twoCreditCardsEnabled,
            "boletoCreditCardEnabled" => $this->boletoCreditCardEnabled,
            "testMode" => $this->testMode,
            "hubInstallId" => $this->hubInstallId->getValue(),
            "keys" => $this->keys,
            "cardConfigs" => $this->cardConfigs
        ];
    }

    public function updateFromSettings(Configuration $config)
    {
        $this->cardConfigs = [];
        foreach ($config->getCardConfigs() as $cardConfig) {
            $this->addCardConfig($cardConfig);
        }

        $this->setBoletoEnabled($config->isBoletoEnabled());
        $this->setCreditCardEnabled($config->isCreditCardEnabled());
        $this->setBoletoCreditCardEnabled($config->isBoletoCreditCardEnabled());
        $this->setTwoCreditCardsEnabled($config->isTwoCreditCardsEnabled());
    }
}