<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;

class PlanFactory implements FactoryInterface
{
    /**
     *
     * @param  array $postData
     * @return Plan
     */
    public function createFromPostData($postData)
    {
        /*$savedCard = new SavedCard();

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

        if (isset($postData->created_at)) {
            $createdAt = new \Datetime($postData->created_at);
            $createdAt->setTimezone(MPSetup::getStoreTimezone());
            $savedCard->setCreatedAt($createdAt);
        }

        return $savedCard;*/
        return new \stdClass();

    }

    /**
     *
     * @param  array $dbData
     * @return SavedCard
     */
    public function createFromDbData($dbData)
    {
        /*$savedCard = new SavedCard();

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

        if (!empty($dbData['created_at'])) {
            $createdAt = \Datetime::createFromFormat(
                SavedCard::DATE_FORMAT,
                $dbData['created_at']
            );
            $savedCard->setCreatedAt($createdAt);
        }

        return $savedCard;*/
    }
}
