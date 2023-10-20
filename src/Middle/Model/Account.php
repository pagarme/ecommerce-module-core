<?php

namespace Pagarme\Core\Middle\Model;

use Pagarme\Core\Middle\Model\Account\PaymentEnum;
use Pagarme\Core\Middle\Model\Account\PaymentMethodSettings;
use Pagarme\Core\Middle\Model\Account\StoreSettings;
use PagarmeCoreApiLib\Models\GetAccountResponse;

class Account extends ModelWithErrors
{
    const ACCOUNT_DISABLED = 'accountDisabled';

    const DOMAIN_EMPTY = 'domainEmpty';

    const DOMAIN_INCORRECT = 'domainIncorrect';

    const WEBHOOK_INCORRECT = 'webhookIncorrect';

    const MULTIPAYMENTS_DISABLED = 'multiPaymentsDisabled';

    const MULTIBUYERS_DISABLED = 'multiBuyersDisabled';

    /**
     * @var bool
     */
    private $accountEnabled;

    /**
     * @var bool
     */
    private $multiPaymentsEnabled;

    /**
     * @var bool
     */
    private $multiBuyerEnabled;

    /**
     * @var array
     */
    private $domains;

    /**
     * @var array
     */
    private $webhooks;

    /**
     * @var PaymentMethodSettings
     */
    private $creditCardSettings;

    /**
     * @var PaymentMethodSettings
     */
    private $billetSettings;

    /**
     * @var PaymentMethodSettings
     */
    private $pixSettings;

    /**
     * @var PaymentMethodSettings
     */
    private $voucherSettings;

    /**
     * @var PaymentMethodSettings
     */
    private $debitCardSettings;

    /**
     * @return bool
     */
    public function isAccountEnabled()
    {
        return $this->accountEnabled;
    }

    /**
     * @param mixed $accountEnabled
     */
    public function setAccountEnabled($accountEnabled)
    {
        if (is_string($accountEnabled)) {
            $this->accountEnabled = $accountEnabled === 'active';
            return;
        }
        $this->accountEnabled = $accountEnabled;
    }

    /**
     * @return bool
     */
    public function isMultiPaymentsEnabled()
    {
        return $this->multiPaymentsEnabled;
    }

    /**
     * @param bool $multiPaymentsEnabled
     */
    public function setMultiPaymentsEnabled($multiPaymentsEnabled)
    {
        $this->multiPaymentsEnabled = $multiPaymentsEnabled;
    }

    /**
     * @return bool
     */
    public function isMultiBuyerEnabled()
    {
        return $this->multiBuyerEnabled;
    }

    /**
     * @param mixed $multiBuyerEnabled
     */
    public function setMultiBuyerEnabled($multiBuyerEnabled)
    {
        $this->multiBuyerEnabled = $multiBuyerEnabled;
    }

    /**
     * @return array
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param array $domains
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;
    }

    /**
     * @return array
     */
    public function getWebhooks()
    {
        return $this->webhooks;
    }

    /**
     * @param array $webhooks
     */
    public function setWebhooks($webhooks)
    {
        $this->webhooks = $webhooks;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getCreditCardSettings()
    {
        return $this->creditCardSettings;
    }

    /**
     * @param PaymentMethodSettings $creditCardSettings
     */
    public function setCreditCardSettings($creditCardSettings)
    {
        $this->creditCardSettings = $creditCardSettings;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getBilletSettings()
    {
        return $this->billetSettings;
    }

    /**
     * @param PaymentMethodSettings $billetSettings
     */
    public function setBilletSettings($billetSettings)
    {
        $this->billetSettings = $billetSettings;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getPixSettings()
    {
        return $this->pixSettings;
    }

    /**
     * @param PaymentMethodSettings $pixSettings
     */
    public function setPixSettings($pixSettings)
    {
        $this->pixSettings = $pixSettings;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getVoucherSettings()
    {
        return $this->voucherSettings;
    }

    /**
     * @param PaymentMethodSettings $voucherSettings
     */
    public function setVoucherSettings($voucherSettings)
    {
        $this->voucherSettings = $voucherSettings;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getDebitCardSettings()
    {
        return $this->debitCardSettings;
    }

    /**
     * @param PaymentMethodSettings $debitCardSettings
     */
    public function setDebitCardSettings($debitCardSettings)
    {
        $this->debitCardSettings = $debitCardSettings;
    }

    public function validate(StoreSettings $storeSettings = null)
    {
        $this->validateAccountEnabled();
        $this->validateDomain($storeSettings);
        $this->validateWebhooks($storeSettings);
        $this->validateMultiBuyer();
        $this->validateMultiPayments();

        if ($storeSettings) {
            $this->setError($this->getCreditCardSettings()->validate($storeSettings));
            $this->setError($this->getBilletSettings()->validate($storeSettings));
            $this->setError($this->getPixSettings()->validate($storeSettings));
            $this->setError($this->getVoucherSettings()->validate($storeSettings));
            $this->setError($this->getDebitCardSettings()->validate($storeSettings));
        }

        return $this;
    }

    private function validateAccountEnabled()
    {
        if (!$this->isAccountEnabled()) {
            $this->setError(self::ACCOUNT_DISABLED);
        }
    }

    private function validateDomain(StoreSettings $storeSettings = null)
    {
        $domains = $this->getDomains();
        if (empty($domains) && (empty($storeSettings) || !$storeSettings->isSandbox())) {
            $this->setError(self::DOMAIN_EMPTY);
            return;
        }

        if ($this->canNotValidateUrlSetting($storeSettings)) {
            return;
        }

        $siteUrls = $storeSettings->getStoreUrls();
        foreach ($domains as $domain) {
            foreach ($siteUrls as $siteUrl) {
                if (strpos($domain, $siteUrl) !== false) {
                    return;
                }
            }
        }

        $this->setError(self::DOMAIN_INCORRECT);
    }

    private function validateWebhooks(StoreSettings $storeSettings = null)
    {
        if ($this->canNotValidateUrlSetting($storeSettings)) {
            return;
        }

        $siteUrls = $storeSettings->getStoreUrls();
        foreach ($this->getWebhooks() as $webhook) {
            if ($webhook->status !== 'active') {
                continue;
            }
            foreach ($siteUrls as $siteUrl) {
                if (strpos($webhook->url, $siteUrl) !== false) {
                    return;
                }
            }
        }

        $this->setError(self::WEBHOOK_INCORRECT);
    }

    private function validateMultiBuyer()
    {
        $this->validateEnabledSetting('MultiBuyer', self::MULTIBUYERS_DISABLED);
    }

    private function validateMultiPayments()
    {
        $this->validateEnabledSetting('MultiPayments', self::MULTIPAYMENTS_DISABLED);
    }

    private function canNotValidateUrlSetting(StoreSettings $storeSettings = null)
    {
        return !$storeSettings || $storeSettings->isSandbox();
    }

    private function validateEnabledSetting($setting, $error)
    {
        $methodName = "is{$setting}Enabled";
        if (!$this->$methodName()) {
            $this->setError($error);
        }
    }

    public static function createFromSdk(GetAccountResponse $accountInfo)
    {
        $account = new Account();
        $orderSettings = $accountInfo->orderSettings;
        $account->setAccountEnabled($accountInfo->status);
        $account->setMultiPaymentsEnabled($orderSettings['multi_payments_enabled']);
        $account->setMultiBuyerEnabled($orderSettings['multi_buyers_enabled']);
        $account->setDomains($accountInfo->domains);
        $account->setWebhooks($accountInfo->webhookSettings);
        $account->setCreditCardSettings(PaymentMethodSettings::createFromSdk($accountInfo, PaymentEnum::CREDIT_CARD));
        $account->setBilletSettings(
            PaymentMethodSettings::createFromSdk(
                $accountInfo,
                PaymentEnum::BILLET,
                PaymentEnum::BILLET_ACCOUNT
            )
        );
        $account->setPixSettings(PaymentMethodSettings::createFromSdk($accountInfo, PaymentEnum::PIX));
        $account->setVoucherSettings(PaymentMethodSettings::createFromSdk($accountInfo, PaymentEnum::VOUCHER));
        $account->setDebitCardSettings(PaymentMethodSettings::createFromSdk($accountInfo, PaymentEnum::DEBIT_CARD));

        return $account;
    }
}
