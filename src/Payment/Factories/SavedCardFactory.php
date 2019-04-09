<?php

namespace Mundipagg\Core\Payment\Factories;

use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Kernel\ValueObjects\NumericString;
use Mundipagg\Core\Payment\Aggregates\SavedCard;
use Mundipagg\Core\Payment\ValueObjects\CardId;

class SavedCardFactory implements FactoryInterface
{
    /**
     *
     * @param  \stdClass $postData
     * @return SavedCard
     */
    public function createFromPostData($postData)
    {
        $savedCard = new SavedCard();

        $savedCard->setMundipaggId(
            new CardId($postData->id)
        );

        $savedCard->setOwnerId(
            new CustomerId($postData->owner)
        );

        $brand = strtolower($postData->brand);
        $savedCard->setBrand(CardBrand::$brand());
        $savedCard->setOwnerName($postData->holder_name);
        $savedCard->setFirstSixDigits(
            new NumericString($postData->first_six_digits)
        );
        $savedCard->setLastFourDigits(
            new NumericString($postData->last_four_digits)
        );

        return $savedCard;
    }

    /**
     *
     * @param  array $dbData
     * @return SavedCard
     */
    public function createFromDbData($dbData)
    {
        $savedCard = new SavedCard();

        $savedCard->setId($dbData['id']);

        $savedCard->setMundipaggId(
            new CardId($dbData['mundipagg_id'])
        );

        $savedCard->setOwnerId(
            new CustomerId($dbData['owner_id'])
        );

        $brand = strtolower($dbData['brand']);
        $savedCard->setOwnerName($dbData['owner_name']);
        $savedCard->setBrand(CardBrand::$brand());
        $savedCard->setFirstSixDigits(
            new NumericString($dbData['first_six_digits'])
        );
        $savedCard->setLastFourDigits(
            new NumericString($dbData['last_four_digits'])
        );

        return $savedCard;
    }
}
