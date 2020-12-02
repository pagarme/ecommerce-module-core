<?php

namespace Mundipagg\Core\Payment\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;
use Mundipagg\Core\Payment\Factories\SavedCardFactory;
use Mundipagg\Core\Payment\Repositories\SavedCardRepository;

class CardService
{
    private $logService;

    public function __construct()
    {
        $this->logService = $this->getLogService();
    }

    public function getBrandsAvailables(AbstractEntity $config)
    {
        $brandsAvailables = [];
        $cardConfigs = $config->getCardConfigs();

        foreach ($cardConfigs as $cardConfig) {
            if (
                $cardConfig->isEnabled() &&
                !$cardConfig->getBrand()->equals(CardBrand::nobrand())
            ) {
                $brandsAvailables[] = $cardConfig->getBrand()->getName();
            }
        }

        return $brandsAvailables;
    }

    public function saveCards(Order $order)
    {
        $savedCardFactory = new SavedCardFactory();
        $savedCardRepository = new SavedCardRepository();
        $charges = $order->getCharges();

        foreach ($charges as $charge) {
            $lastTransaction = $charge->getLastTransaction();
            if ($lastTransaction === null) {
                continue;
            }

            if (
            !(
                $lastTransaction->getTransactionType()->equals(TransactionType::creditCard()) ||
                $lastTransaction->getTransactionType()->equals(TransactionType::voucher()) ||
                $lastTransaction->getTransactionType()->equals(TransactionType::debitCard())
            )
            ) {
                continue; //save only credit card transactions;
            }

            $metadata = $charge->getMetadata();
            $saveOnSuccess =
                isset($metadata->saveOnSuccess) &&
                $metadata->saveOnSuccess === "true";

            if (
                !empty($lastTransaction->getCardData()) &&
                $saveOnSuccess &&
                $order->getCustomer()->getMundipaggId()->equals(
                    $charge->getCustomer()->getMundipaggId()
                )
            ) {
                $postData =
                    json_decode(json_encode($lastTransaction->getCardData()));
                $postData->owner =
                    $charge->getCustomer()->getMundipaggId();

                $savedCard = $savedCardFactory->createFromTransactionJson($postData);
                if (
                    $savedCardRepository->findByMundipaggId($savedCard->getMundipaggId()) === null
                ) {
                    $savedCardRepository->save($savedCard);
                    $this->logService->info(
                        $order->getCode(),
                        "Card '{$savedCard->getMundipaggId()->getValue()}' saved."
                    );

                }
            }
        }
    }

    public function getLogService()
    {
        return new LogService("Card Service", true);
    }
}