<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use MundiAPILib\Models\CreatePaymentRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use Pagarme\Core\Payment\Interfaces\HaveOrderInterface;
use Pagarme\Core\Payment\Traits\WithAmountTrait;
use Pagarme\Core\Payment\Traits\WithCustomerTrait;
use Pagarme\Core\Payment\Traits\WithOrderTrait;

abstract class AbstractPayment
    extends AbstractEntity
    implements ConvertibleToSDKRequestsInterface, HaveOrderInterface
{
    use WithAmountTrait;
    use WithCustomerTrait;
    use WithOrderTrait;

    protected $moduleConfig;

    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->orderCode = $this->order->getCode();
        $obj->paymentMethod = static::getBaseCode();
        $obj->amount = $this->getAmount();

        $customer = $this->getCustomer();
        if ($customer !== null) {
            $obj->customer = $customer;
        }

        return $obj;
    }

    abstract static public function getBaseCode();

    /**
     * @return CreatePaymentRequest
     */
    public function convertToSDKRequest()
    {
        $this->moduleConfig = MPSetup::getModuleConfiguration();
        $newPayment = new CreatePaymentRequest();
        $newPayment->amount = $this->getAmount();

        $primitive = static::getBaseCode();
        $newPayment->$primitive = $this->convertToPrimitivePaymentRequest();
        $newPayment->paymentMethod = $this->cammel2SnakeCase($primitive);

        if ($this->getCustomer() !== null) {
            $newPayment->customer = $this->getCustomer()->convertToSDKRequest();
        }

        if ($this->moduleConfig->getMarketplaceConfig()->isEnabled()) {
            $newPayment->split = static::getSplitData();
        }

        $newPayment->metadata = static::getMetadata();
        return $newPayment;
    }

    abstract protected function convertToPrimitivePaymentRequest();

    protected function getSplitData()
    {
        $splitMainChargeProcessingFeeOptionConfig = $this->moduleConfig
            ->getMarketplaceConfig()
            ->getSplitMainOptionConfig('responsibilityForProcessingFees');
        $splitMainLiableOptionConfig = $this->moduleConfig
            ->getMarketplaceConfig()
            ->getSplitMainOptionConfig('responsibilityForChargebacks');

        $splitMainRecipient = new \stdClass;
        $splitMainRecipient->amount = 10;
        $splitMainRecipient->recipient_id = "rp_9XYzdWJueuNge7m1";
        $splitMainRecipient->type = "percentage";
        $splitMainRecipient->options = new \stdClass;
        $splitMainRecipient->options->charge_processing_fee =
            $splitMainChargeProcessingFeeOptionConfig;
        $splitMainRecipient->options->charge_remainder_fee = true;
        $splitMainRecipient->options->liable = $splitMainLiableOptionConfig;

        $splitSecondaryChargeProcessingFeeOptionConfig = $this->moduleConfig
            ->getMarketplaceConfig()
            ->getSplitSecondaryOptionConfig('responsibilityForProcessingFees');
        $splitSecondaryLiableOptionConfig = $this->moduleConfig
            ->getMarketplaceConfig()
            ->getSplitSecondaryOptionConfig('responsibilityForChargebacks');

        $splitRecipient1 = new \stdClass;
        $splitRecipient1->amount = 70;
        $splitRecipient1->recipient_id = "rp_eoKMZveFQFKNRp4D";
        $splitRecipient1->type = "percentage";
        $splitRecipient1->options = new \stdClass;
        $splitRecipient1->options->charge_processing_fee =
            $splitSecondaryChargeProcessingFeeOptionConfig;
        $splitRecipient1->options->charge_remainder_fee = false;
        $splitRecipient1->options->liable =
            $splitSecondaryLiableOptionConfig;

        $splitRecipient2 = new \stdClass;
        $splitRecipient2->amount = 20;
        $splitRecipient2->recipient_id = "rp_rNqy0J3i1i7GLVOM";
        $splitRecipient2->type = "percentage";
        $splitRecipient2->options = new \stdClass;
        $splitRecipient2->options->charge_processing_fee =
            $splitSecondaryChargeProcessingFeeOptionConfig;
        $splitRecipient2->options->charge_remainder_fee = false;
        $splitRecipient2->options->liable =
            $splitSecondaryLiableOptionConfig;

        return [$splitMainRecipient, $splitRecipient1, $splitRecipient2];
    }

    protected function getMetadata()
    {
        return null;
    }

    private function cammel2SnakeCase($cammelCaseString)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $cammelCaseString, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}
