<?php

namespace Mundipagg\Core\Payment\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Configuration;
use Mundipagg\Core\Kernel\Services\InstallmentService;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Payments\AbstractCreditCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\BoletoPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\NewCreditCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\NewVoucherPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\SavedCreditCardPayment;
use Mundipagg\Core\Payment\ValueObjects\BoletoBank;
use Mundipagg\Core\Payment\ValueObjects\CardId;
use Mundipagg\Core\Payment\ValueObjects\CardToken;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use Mundipagg\Core\Payment\ValueObjects\PaymentMethod;

final class PaymentFactory
{
    /** @var string[] */
    private $primitiveFactories;
    /** @var Configuration  */
    private $moduleConfig;
    /** @var string */
    private $cardStatementDescriptor;

    /** @var BoletoBank */
    private $boletoBank;

    /** @var string */
    private $boletoInstructions;

    public function __construct()
    {
        $this->primitiveFactories = [
            'createCreditCardPayments',
            'createBoletoPayments',
            'createVoucherPayments',
        ];

        $this->moduleConfig = MPSetup::getModuleConfiguration();

        $this->cardStatementDescriptor = $this->moduleConfig->getCardStatementDescriptor();
        $this->boletoBank = BoletoBank::itau();
        $this->boletoInstructions = $this->moduleConfig->getBoletoInstructions();
    }

    public function createFromJson($json)
    {
        $data = json_decode($json);

        $payments = [];

        foreach ($this->primitiveFactories as $creator) {
            $payments = array_merge($payments, $this->$creator($data));
        }

        return $payments;
    }

    private function createCreditCardPayments($data)
    {
        $cardDataIndex = AbstractCreditCardPayment::getBaseCode();

        if (!isset($data->$cardDataIndex)) {
            return [];
        }

        $capture = $this->moduleConfig->isCapture();

        $cardsData = $data->$cardDataIndex;

        $payments = [];
        foreach ($cardsData as $cardData) {
            $payment = $this->createBaseCardPayment($cardData, $cardDataIndex);

            if ($payment === null) {
                continue;
            }

            $customer = $this->createCustomer($cardData);
            if ($customer !== null) {
                $payment->setCustomer($customer);
            }

            $brand = $cardData->brand;
            $payment->setBrand(CardBrand::$brand());

            $payment->setCapture($capture);
            $payment->setAmount($cardData->amount);
            $payment->setInstallments($cardData->installments);

            //setting amount with interest
            $payment->setAmount(
                $this->getAmountWithInterestForCreditCard($payment)
            );

            $payment->setStatementDescriptor($this->cardStatementDescriptor);

            $payments[] = $payment;
        }

        return $payments;
    }

    private function createVoucherPayments($data)
    {
        $cardDataIndex = NewVoucherPayment::getBaseCode();

        if (!isset($data->$cardDataIndex)) {
            return [];
        }

        $capture = $this->moduleConfig
            ->getVoucherConfig()
            ->isCapture();

        $cardsData = $data->$cardDataIndex;

        $payments = [];
        foreach ($cardsData as $cardData) {
            $payment = $this->createBaseCardPayment($cardData, $cardDataIndex);

            if ($payment === null) {
                continue;
            }

            $customer = $this->createCustomer($cardData);
            if ($customer !== null) {
                $payment->setCustomer($customer);
            }

            $brand = $cardData->brand;
            $payment->setBrand(CardBrand::$brand());

            $payment->setCapture($capture);
            $payment->setAmount($cardData->amount);
            $payment->setInstallments($cardData->installments);

            $payment->setStatementDescriptor($this->cardStatementDescriptor);

            $payments[] = $payment;
        }

        return $payments;
    }

    private function createCustomer($paymentData)
    {
        $multibuyerEnabled = MPSetup::getModuleConfiguration()->isMultiBuyer();
        if (empty($paymentData->customer) || !$multibuyerEnabled) {
            return null;
        }

        $customerFactory = new CustomerFactory();

        return $customerFactory->createFromJson(json_encode($paymentData->customer));
    }

    private function getAmountWithInterestForCreditCard(
        AbstractCreditCardPayment $payment
    )
    {
        $installmentService = new InstallmentService();

        $validInstallments = $installmentService->getInstallmentsFor(
            null,
            $payment->getBrand(),
            $payment->getAmount()
        );

        foreach ($validInstallments as $validInstallment) {
            if ($validInstallment->getTimes() === $payment->getInstallments()) {
                return $validInstallment->getTotal();
            }
        }

        throw new \Exception('Invalid installment number!');
    }

    private function createBoletoPayments($data)
    {
        $boletoDataIndex = BoletoPayment::getBaseCode();

        if (!isset($data->$boletoDataIndex)) {
            return [];
        }

        $boletosData = $data->$boletoDataIndex;

        $payments = [];
        foreach ($boletosData as $boletoData) {
            $payment = new BoletoPayment();

            $customer = $this->createCustomer($boletoData);
            if ($customer !== null) {
                $payment->setCustomer($customer);
            }

            $payment->setAmount($boletoData->amount);
            $payment->setBank($this->boletoBank);
            $payment->setInstructions($this->boletoInstructions);

            $payments[] = $payment;
        }

        return $payments;
    }

    /**
     * @param $identifier
     * @return AbstractCreditCardPayment|null
     */
    private function createBaseCardPayment($data, $method)
    {
        $identifier = $data->identifier;
        try {
            $cardToken = new CardToken($identifier);
            $payment =  $this->getNewPaymentMethod($method);
            $payment->setIdentifier($cardToken);

            if (isset($data->saveOnSuccess)) {
                $payment->setSaveOnSuccess($data->saveOnSuccess);
            }
            return $payment;
        } catch (\Throwable $e)
        {

        }

        try {
            $cardId = new CardId($identifier);
            $payment =  $this->getSavedPaymentMethod($method);
            $payment->setIdentifier($cardId);
            $owner = new CustomerId($data->customerId);
            $payment->setOwner($owner);

            return $payment;
        } catch (\Throwable $e)
        {

        }

        return null;
    }

    /**
     * @param $method
     * @return SavedCreditCardPayment
     * @todo Add voucher saved payment
     */
    private function getSavedPaymentMethod($method)
    {
        return new SavedCreditCardPayment();
    }

    /**
     * @param $method
     * @return NewCreditCardPayment|NewVoucherPayment
     */
    private function getNewPaymentMethod($method)
    {
        if ($method == PaymentMethod::CREDIT_CARD) {
            return new NewCreditCardPayment();
        }
        return new NewVoucherPayment();
    }
}