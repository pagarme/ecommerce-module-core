<?php

namespace Pagarme\Core\Marketplace\Aggregates;

use MundiAPILib\Models\CreateSplitOptionsRequest;
use MundiAPILib\Models\CreateSplitRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Marketplace\Interfaces\ConvertibleToSDKRequestsInterface;

class Split extends AbstractEntity
{
    private $sellersData = [];
    private $marketplaceData = [];
    private $commission = 0;
    private $recipientId = '';

    protected $moduleConfig;

    public function __construct()
    {
        $this->moduleConfig = MPSetup::getModuleConfiguration();
    }

    public function getMainChargeProcessingFeeOptionConfig()
    {
        return $this->moduleConfig
            ->getMarketplaceConfig()
            ->getSplitMainOptionConfig('responsibilityForProcessingFees');
    }

    public function getMainLiableOptionConfig()
    {
        return $this->moduleConfig
            ->getMarketplaceConfig()
            ->getSplitMainOptionConfig('responsibilityForChargebacks');
    }

    public function getSecondaryChargeProcessingFeeOptionConfig()
    {
        return $this->moduleConfig
            ->getMarketplaceConfig()
            ->getSplitSecondaryOptionConfig('responsibilityForProcessingFees');
    }

    public function getSecondaryLiableOptionConfig()
    {
        return $this->moduleConfig
            ->getMarketplaceConfig()
            ->getSplitSecondaryOptionConfig('responsibilityForChargebacks');
    }

    public function getSellersData()
    {
        return $this->sellersData;
    }

    public function setSellersData($sellersData)
    {
        $this->sellersData = $sellersData;
    }

    public function getMarketplaceData()
    {
        return $this->marketplaceData;
    }

    public function setMarketplaceData($marketplaceData)
    {
       $this->marketplaceData = $marketplaceData;
    }

    public function getMarketplaceComission()
    {
        $marketplaceData = $this->marketplaceData;
        return $marketplaceData['marketplaceCommission'];
    }

    public function setCommission($commission)
    {
        $this->commission = $commission;
    }

    public function getCommission()
    {
        return $this->commission;
    }

    public function setRecipientId($recipientId)
    {
        $this->recipientId = $recipientId;
    }

    public function getRecipientId()
    {
        return $this->recipientId;
    }

    public function convertMainToSDKRequest()
    {
        $splitRequest = new CreateSplitRequest();

        $splitRequest->type = 'flat';
        $splitRequest->recipientId = $this->getRecipientId();
        $splitRequest->amount = $this->getCommission();

        $splitRequest->options = new CreateSplitOptionsRequest();

        $splitRequest->options->chargeProcessingFee = $this->getMainChargeProcessingFeeOptionConfig();
        $splitRequest->options->liable = $this->getMainLiableOptionConfig();
        $splitRequest->options->chargeRemainderFee = true;

        return $splitRequest;
    }

    public function convertSecondaryToSDKRequest()
    {
        $splitRequest = new CreateSplitRequest();

        $splitRequest->type = 'flat';
        $splitRequest->recipientId = $this->getRecipientId();
        $splitRequest->amount = $this->getCommission();

        $splitRequest->options = new CreateSplitOptionsRequest();

        $splitRequest->options->chargeProcessingFee = $this->getSecondaryChargeProcessingFeeOptionConfig();
        $splitRequest->options->liable = $this->getSecondaryLiableOptionConfig();
        $splitRequest->options->chargeRemainderFee = false;

        return $splitRequest;
    }

    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->sellersData = $this->getSellersData();
        $obj->marketplaceData = $this->getMarketplaceData();

        return $obj;
    }
}
