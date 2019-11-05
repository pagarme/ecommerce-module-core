<?php


namespace Mundipagg\Core\Recurrence\Factories;


use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Aggregates\SubProductSubscription;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class SubProductSubscriptionFactory implements FactoryInterface
{
    /**
     *
     * @param array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData)
    {
        $subProductSubscription = new SubProductSubscription();

        if (!empty($postData['productSubscriptionId'])) {
            $subProductSubscription->setProductSubscriptionId($postData['productSubscriptionId']);
        }

        if (!empty($postData['id'])) {
            $subProductSubscription->setId($postData['id']);
        }

        if (!empty($postData['product_id'])) {
            $subProductSubscription->setProductId($postData['product_id']);
        }

        if (!empty($postData['name'])) {
            $subProductSubscription->setName($postData['name']);
        }

        if (!empty($postData['description'])) {
            $subProductSubscription->setDescription($postData['description']);
        }

        if (!empty($postData['price'])) {
            $subProductSubscription->setPrice($postData['price']);
        }

        if (!empty($postData['quantity'])) {
            $subProductSubscription->setQuantity($postData['quantity']);
        }

        if (!empty($postData['cycles'])) {
            $subProductSubscription->setCycles($postData['cycles']);
        }


        return $subProductSubscription;
    }

    /**
     *
     * @param array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }

    protected function setPaymentMethods(&$productSubscription, $postData)
    {
        if (!empty($postData['payment_method']['credit_card'])) {
            $productSubscription->setAcceptCreditCard(true);

            if (!empty($postData['allow_installments'])) {
                $productSubscription->setAllowInstallments(true);
            }
        }

        if (!empty($postData['payment_method']['boleto'])) {
            $productSubscription->setAcceptBoleto(true);
        }
    }

    protected function setRepetitions(&$productSubscription, $repetitions)
    {
        foreach ($repetitions as $repetition) {
            if (!empty($repetition['interval_count'])) {
                $repetitionEntity = new Repetition();

                if (!empty($productSubscription->getId())) {
                    $repetitionEntity->setSubscriptionId($productSubscription->getId());
                }

                $intervalType = $repetition['interval'];
                $interval = IntervalValueObject::$intervalType($repetition['interval_count']);

                $discountType = $repetition['discount_type'];
                $discount = DiscountValueObject::$discountType($repetition['discount_value']);

                $repetitionEntity->setInterval($interval);
                $repetitionEntity->setDiscount($discount);

                $productSubscription->addRepetition($repetitionEntity);
            }
        }
    }
}