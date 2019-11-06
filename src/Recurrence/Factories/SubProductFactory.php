<?php


namespace Mundipagg\Core\Recurrence\Factories;


use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class SubProductFactory implements FactoryInterface
{
    /**
     *
     * @param array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData)
    {
        $subProduct = new SubProduct();

        if (!empty($postData['productRecurrenceId'])) {
            $subProduct->setProductRecurrenceId($postData['productRecurrenceId']);
        }

        if (!empty($postData['id'])) {
            $subProduct->setId($postData['id']);
        }

        if (!empty($postData['product_id'])) {
            $subProduct->setProductId($postData['product_id']);
        }

        if (!empty($postData['name'])) {
            $subProduct->setName($postData['name']);
        }

        if (!empty($postData['description'])) {
            $subProduct->setDescription($postData['description']);
        }

        if (!empty($postData['price'])) {
            $subProduct->setPrice($postData['price']);
        }

        if (!empty($postData['quantity'])) {
            $subProduct->setQuantity($postData['quantity']);
        }

        if (!empty($postData['cycles'])) {
            $subProduct->setCycles($postData['cycles']);
        }


        return $subProduct;
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