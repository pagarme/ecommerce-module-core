<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use MundiAPILib\Models\CreatePaymentRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Marketplace\Aggregates\Split;
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
            $newPayment->split = $this->extractRequestsFromArray(
                $newPayment->split
            );
        }

        $newPayment->metadata = static::getMetadata();
        return $newPayment;
    }

    abstract protected function convertToPrimitivePaymentRequest();

    protected function getSplitData()
    {
        $splitOrderData = $this->order->getSplitData();

        if(!$splitOrderData) {
            return null;
        }

        $splitMainRecipient = new Split();
        $splitMainRecipient->setCommission(
            $splitOrderData->getMarketplaceComission()
        );
        $splitMainRecipient->setRecipientId("rp_9XYzdWJueuNge7m1");
        $splitMainRecipientRequest = $splitMainRecipient
            ->convertMainToSDKRequest();

        foreach ($splitOrderData->getSellersData() as $seller) {
            $splitRecipient = new Split();
            $splitRecipient->setCommission($seller['sellerCommission']);
            $splitRecipient->setRecipientId($seller['pagarmeId']);
            $splitRecipientRequests[] = $splitRecipient
                ->convertSecondaryToSDKRequest();
        }

        return [$splitMainRecipientRequest, $splitRecipientRequests];
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

    /**
     * @param array|null $splitArray
     * @return array|null
     */
    private function extractRequestsFromArray($splitArray)
    {
        if (is_null($splitArray)) {
            return null;
        }

        $splitRecipientRequests = $splitArray[1];

        foreach ($splitRecipientRequests as $request) {
            array_push($splitArray,
                $request
            );
        }

        unset($splitArray[1]);
        $splitArray = array_values($splitArray);
        return $splitArray;
    }
}
