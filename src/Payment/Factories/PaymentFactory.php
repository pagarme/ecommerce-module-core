<?php

namespace Mundipagg\Core\Payment\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Kernel\Aggregates\Configuration;
use Mundipagg\Core\Payment\Aggregates\Payments\AbstractCreditCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\BoletoPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\NewCreditCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\SavedCreditCardPayment;
use Mundipagg\Core\Payment\ValueObjects\BoletoBank;
use Mundipagg\Core\Payment\ValueObjects\CardId;
use Mundipagg\Core\Payment\ValueObjects\CardToken;

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
            'createBoletoPayments'
        ];

        $this->moduleConfig = AbstractModuleCoreSetup::getModuleConfiguration();

        //@todo get these from config.
        $this->cardStatementDescriptor = 'STATEMENT DESC';
        $this->boletoBank = BoletoBank::itau();
        $this->boletoInstructions = 'BOLETO instructions';
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

        $cardsData = $data->$cardDataIndex;

        $payments = [];
        foreach ($cardsData as $cardData) {
            $payment = $this->createBaseCardPayment($cardData->identifier);
            if ($payment === null) {
                continue;
            }

            $payment->setAmount($cardData->amount);
            $payment->setInstallments($cardData->installments);
            $payment->setStatementDescriptor($this->cardStatementDescriptor);

            $payments[] = $payment;
        }

        return $payments;
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
    private function createBaseCardPayment($identifier)
    {
        try {
            $cardToken = new CardToken($identifier);
            $payment =  new NewCreditCardPayment();
            $payment->setIdentifier($cardToken);

            return $payment;
        } catch (\Throwable $e)
        {

        }

        try {
            $cardId = new CardId($identifier);
            $payment =  new SavedCreditCardPayment();
            $payment->setIdentifier($cardId);

            return $payment;
        } catch (\Throwable $e)
        {

        }

        return null;
    }
}