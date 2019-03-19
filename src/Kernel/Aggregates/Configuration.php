<?php

namespace Mundipagg\Core\Kernel\Aggregates;

use Exception;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\AddressAttributes;
use Mundipagg\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Mundipagg\Core\Kernel\ValueObjects\Key\AbstractSecretKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\AbstractPublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\PublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\SecretKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Mundipagg\Core\Kernel\ValueObjects\Key\TestSecretKey;
use Mundipagg\Core\Kernel\ValueObjects\Id\GUID;

final class Configuration extends AbstractEntity
{
    const KEY_SECRET = 'KEY_SECRET';
    const KEY_PUBLIC = 'KEY_PUBLIC';

    const CARD_OPERATION_AUTH_ONLY = 'auth_only';
    const CARD_OPERATION_AUTH_AND_CAPTURE = 'auth_and_capture';

    /**
     *
     * @var bool 
     */
    private $enabled;
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

    /** @var string */
    private $cardOperation;

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


    /**
     * @var bool
     */
    private $antifraudEnabled;

    /**
     * @var int
     */
    private $antifraudMinAmount;

    /** @var bool */
    private $installmentsEnabled;

    /** @var AddressAttributes */
    private $addressAttributes;

    /** @var string */
    private $cardStatementDescriptor;

    /** @var string */
    private $boletoInstructions;

    /** @var bool */
    private $saveCards;

    public function __construct()
    {
        $this->cardConfigs = [];

        $this->keys = [
            self::KEY_SECRET => null,
            self::KEY_PUBLIC => null,
        ];

        $this->testMode = true;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = filter_var(
            $enabled,
            FILTER_VALIDATE_BOOLEAN
        );
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
    public function setPublicKey(AbstractPublicKey $key)
    {
        $this->testMode = false;

        $this->keys[self::KEY_PUBLIC] = $key;

        if (is_a($key, TestPublicKey::class)) {
            $this->testMode = true;
        };

        return $this;
    }

    /**
     *
     * @param  string|array $key
     * @return $this
     */
    public function setSecretKey(AbstractSecretKey $key)
    {
        $this->keys[self::KEY_SECRET] = $key;
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
     * @return bool
     */
    public function isHubEnabled()
    {
        if ($this->hubInstallId === null) {
            return false;
        }
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
        $cardConfigs = $this->getCardConfigs();
        foreach ($cardConfigs as $cardConfig) {
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
        return $this->cardConfigs !== null ? $this->cardConfigs : [];
    }

    /**
     * @return string
     */
    public function getCardOperation()
    {
        return $this->cardOperation;
    }

    /**
     * @param string $cardOperation
     */
    public function setCardOperation($cardOperation)
    {
        $this->cardOperation = $cardOperation;
    }

    /**
     * @return bool
     */
    public function isCapture()
    {
        return $this->getCardOperation() === self::CARD_OPERATION_AUTH_AND_CAPTURE;
    }

    /**
     * @return bool
     */
    public function isAntifraudEnabled()
    {
        return $this->antifraudEnabled;
    }

    /**
     * @param bool $antifraudEnabled
     */
    public function setAntifraudEnabled($antifraudEnabled)
    {
        $this->antifraudEnabled = $antifraudEnabled;
    }

    /**
     * @return int
     */
    public function getAntifraudMinAmount()
    {
        return $this->antifraudMinAmount;
    }

    /**
     * @param int $antifraudMinAmount
     */
    public function setAntifraudMinAmount(int $antifraudMinAmount)
    {
        if ($antifraudMinAmount < 0) {
        throw new InvalidParamException(
            'AntifraudMinAmount should be at least 0!',
            $antifraudMinAmount
        );
    }
        $this->antifraudMinAmount = $antifraudMinAmount;
    }

    /**
     * @return bool
     */
    public function isInstallmentsEnabled()
    {
        return $this->installmentsEnabled;
    }

    /**
     * @param bool $installmentsEnabled
     */
    public function setInstallmentsEnabled($installmentsEnabled)
    {
        $this->installmentsEnabled = $installmentsEnabled;
    }

    /**
     * @return AddressAttributes
     */
    public function getAddressAttributes()
    {
        return $this->addressAttributes;
    }

    /**
     * @param AddressAttributes $addressAttributes
     */
    public function setAddressAttributes(AddressAttributes $addressAttributes)
    {
        $this->addressAttributes = $addressAttributes;
    }

    /**
     * @return string
     */
    public function getCardStatementDescriptor()
    {
        return $this->cardStatementDescriptor;
    }

    /**
     * @param string $cardStatementDescriptor
     */
    public function setCardStatementDescriptor($cardStatementDescriptor)
    {
        $this->cardStatementDescriptor = $cardStatementDescriptor;
    }

    /**
     * @return string
     */
    public function getBoletoInstructions()
    {
        return $this->boletoInstructions;
    }

    /**
     * @param string $boletoInstructions
     */
    public function setBoletoInstructions($boletoInstructions)
    {
        $this->boletoInstructions = $boletoInstructions;
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
            "enabled" => $this->enabled,
            "antifraudEnabled" => $this->isAntifraudEnabled(),
            "antifraudMinAmount" => $this->getAntifraudMinAmount(),
            "boletoEnabled" => $this->boletoEnabled,
            "creditCardEnabled" => $this->creditCardEnabled,
            "twoCreditCardsEnabled" => $this->twoCreditCardsEnabled,
            "boletoCreditCardEnabled" => $this->boletoCreditCardEnabled,
            "testMode" => $this->testMode,
            "hubInstallId" => $this->isHubEnabled() ? $this->hubInstallId->getValue() : null,
            "addressAttributes" => $this->getAddressAttributes(),
            "keys" => $this->keys,
            "cardOperation" => $this->cardOperation,
            "installmentsEnabled" => $this->isInstallmentsEnabled(),
            "cardStatementDescriptor" => $this->getCardStatementDescriptor(),
            "boletoInstructions" => $this->getBoletoInstructions(),
            "cardConfigs" => $this->getCardConfigs()
        ];
    }
}