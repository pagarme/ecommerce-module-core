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
     * @var ProductSubscription
     */
    protected $productSubscription;

    public function __construct()
    {
        $this->productSubscription = new ProductSubscription();
    }
    /**
     *
     * @param array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData)
    {
        $this->setId($postData);
        $this->setProductId($postData);
        $this->setIsEnabled($postData);
        $this->setPaymentMethods($postData);
        $this->setRepetitions( $postData);
        $this->setItems($postData);

        return $this->productSubscription;
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

    protected function setPaymentMethods($postData)
    {
        if (empty($postData['payment_methods'])) {
            return;
        }

        if (!empty($postData['payment_methods']['credit_card'])) {
            $this->productSubscription->setAcceptCreditCard(true);

            if (!empty($postData['allow_installments'])) {
                $this->productSubscription->setAllowInstallments(true);
            }
        }

        if (!empty($postData['payment_methods']['boleto'])) {
            $this->productSubscription->setAcceptBoleto(true);
        }
    }

    protected function setRepetitions($postData)
    {
        if (empty($postData['intervals'])) {
            return;
        }

        foreach ($postData['intervals'] as $repetition) {

            if (
                empty($repetition['interval_count']) &&
                empty($repetition['interval'])
            ) {
                continue;
            }

            $repetitionEntity = new Repetition();

            if (!empty($this->productSubscription->getId())) {
                $repetitionEntity->setSubscriptionId($this->productSubscription->getId());
            }

            $intervalType = $repetition['interval'];
            $interval = IntervalValueObject::$intervalType($repetition['interval_count']);

            $discountType = $repetition['discount_type'];
            $discount = DiscountValueObject::$discountType($repetition['discount_value']);

            $repetitionEntity->setInterval($interval);
            $repetitionEntity->setDiscount($discount);

            $this->productSubscription->addRepetition($repetitionEntity);
        }
    }

    protected function setItems($postData)
    {
        if (empty($postData['itens'])) {
            return;
        }

        foreach ($postData['itens'] as $item) {
            $subProductFactory = new SubProductSubscriptionFactory();
            $subProduct = $subProductFactory->createFromPostData($item);
            $this->productSubscription->addItems($subProduct);
        }
    }

    public function setId($postData)
    {
        if (!empty($postData['id'])) {
            $this->productSubscription->setId($postData['id']);
        }
    }

    public function setProductId($postData)
    {
        if (!empty($postData['product_bundle_id'])) {
            $this->productSubscription->setProductId($postData['product_bundle_id']);
        }
    }

    public function setIsEnabled($postData)
    {
        if (!empty($postData['enabled'])) {
            $this->productSubscription->setIsEnabled($postData['enabled']);
        }
    }
}