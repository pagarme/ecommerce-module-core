<?php


namespace Mundipagg\Core\Recurrence\Factories;


use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class ProductSubscriptionFactory implements FactoryInterface
{
    /**
     *
     * @param array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData)
    {
        $productSubscription = new ProductSubscription();

        if (!empty($postData['id'])) {
            $productSubscription->setId($postData['id']);
        }

        if (!empty($postData['product_bundle_id'])) {
            $productSubscription->setProductId($postData['product_bundle_id']);
        }

        if (!empty($postData['enabled'])) {
            $productSubscription->setIsEnabled($postData['enabled']);
        }

        if (!empty($postData['payment_methods'])) {
            $this->setPaymentMethods($productSubscription, $postData);
        }

        if (!empty($postData['intervals'])) {
            $this->setRepetitions($productSubscription, $postData['intervals']);
        }

        if (!empty($postData['itens'])) {
            $this->setItems($productSubscription, $postData['itens']);
        }

        return $productSubscription;
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
        if (!empty($postData['payment_methods']['credit_card'])) {
            $productSubscription->setAcceptCreditCard(true);

            if (!empty($postData['allow_installments'])) {
                $productSubscription->setAllowInstallments(true);
            }
        }

        if (!empty($postData['payment_methods']['boleto'])) {
            $productSubscription->setAcceptBoleto(true);
        }
    }

    protected function setRepetitions(&$productSubscription, $repetitions)
    {
        foreach ($repetitions as $repetition) {

            if (
                empty($repetition['interval_count']) &&
                empty($repetition['interval'])
            ) {
                continue;
            }

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

    protected function setItems(&$productSubscription, $items)
    {
        foreach ($items as $item) {
            $subProductFactory = new SubProductSubscriptionFactory();
            $subProduct = $subProductFactory->createFromPostData($item);
            $productSubscription->addItems($subProduct);
        }
    }
}