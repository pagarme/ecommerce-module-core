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

    protected $marketplaceConfig;

    public function __construct()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();
        $this->marketplaceConfig = $moduleConfig->getMarketplaceConfig();
    }

    public function getMainChargeProcessingFeeOptionConfig()
    {
        if (!$this->marketplaceConfig) {
            return null;
        }

        return $this->marketplaceConfig
            ->getSplitMainOptionConfig('responsibilityForProcessingFees');
    }

    public function getMainLiableOptionConfig()
    {
        if (!$this->marketplaceConfig) {
            return null;
        }

        return $this->marketplaceConfig
            ->getSplitMainOptionConfig('responsibilityForChargebacks');
    }

    public function getSecondaryChargeProcessingFeeOptionConfig()
    {
        if (!$this->marketplaceConfig) {
            return null;
        }

        return $this->marketplaceConfig
            ->getSplitSecondaryOptionConfig('responsibilityForProcessingFees');
    }

    public function getSecondaryLiableOptionConfig()
    {
        if (!$this->marketplaceConfig) {
            return null;
        }

        return $this->marketplaceConfig
            ->getSplitSecondaryOptionConfig('responsibilityForChargebacks');
    }

    public function getSellersData()
    {
        return $this->sellersData;
    }

    /**
     * @param array $sellersData
     */
    public function setSellersData($sellersData)
    {
        $this->sellersData = $sellersData;
    }

    public function getMarketplaceData()
    {
        return $this->marketplaceData;
    }

    /**
     * @param array $marketplaceData
     */
    public function setMarketplaceData($marketplaceData)
    {
       $this->marketplaceData = $marketplaceData;
    }

    /**
     * @return int
     */
    public function getMarketplaceComission()
    {
        $marketplaceData = $this->marketplaceData;
        return $marketplaceData['totalCommission'];
    }

    /**
     * @param int $commission
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;
    }

    /**
     * @return int
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @param string $recipientId
     */
    public function setRecipientId($recipientId)
    {
        $this->recipientId = $recipientId;
    }

    /**
     * @return string
     */
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
