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
        if (!is_array($postData)) {
            return;
        }

        $this->setId($postData);
        $this->setProductId($postData);
        $this->setCreditCard($postData);
        $this->setAllowInstallments($postData);
        $this->setBoleto($postData);
        $this->setBillingType($postData);
        $this->setStatus($postData);
        $this->setRepetitions($postData);
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
        return $this->createFromPostData($dbData);
        // TODO: Implement createFromDbData() method.
    }

    protected function setRepetitions($postData)
    {
        if (empty($postData['intervals'])) {
            return;
        }

        foreach ($postData['intervals'] as $repetition) {
            if (
                empty($repetition['interval_count']) ||
                empty($repetition['interval'])
            ) {
                continue;
            }

            $repetitionFactory = new RepetitionFactory();
            $repetitionEntity = $repetitionFactory->createFromPostData($repetition);

            $this->productSubscription->addRepetition($repetitionEntity);
        }
    }

    protected function setItems($postData)
    {
        if (empty($postData['items'])) {
            return;
        }

        foreach ($postData['items'] as $item) {
            $subProductFactory = new SubProductFactory();
            $subProduct = $subProductFactory->createFromPostData($item);
            $this->productSubscription->addItems($subProduct);
        }
    }

    private function setId($postData)
    {
        if (isset($postData['id'])) {
            $this->productSubscription->setId($postData['id']);
            return;
        }
    }

    private function setBillingType($postData)
    {
        $this->productSubscription->setBillingType('PREPAID');
    }

    private function setCreditCard($postData)
    {
        if (isset($postData['credit_card'])) {
            $creditCard = !empty($postData['credit_card']) ? '1' : '0';
            $this->productSubscription->setCreditCard($creditCard);
            return;
        }
    }

    private function setBoleto($postData)
    {
        if (isset($postData['boleto'])) {
            $boleto = !empty($postData['boleto']) ? '1' : '0';
            $this->productSubscription->setBoleto($boleto);
            return;
        }
    }

    private function setAllowInstallments($postData)
    {
        if (isset($postData['installments'])) {
            $installments = !empty($postData['installments']) ? '1' : '0';
            $this->productSubscription->setAllowInstallments($installments);
            return;
        }
    }

    private function setProductId($postData)
    {
        if (isset($postData['product_id'])) {
            $this->productSubscription->setProductId($postData['product_id']);
            return;
        }
    }

    private function setUpdatedAt($postData)
    {
        if (isset($postData['updated_at'])) {
            $this->productSubscription->setUpdatedAt(new \Datetime($postData['updated_at']));
            return;
        }
    }

    private function setCreatedAt($postData)
    {
        if (isset($postData['created_at'])) {
            $this->productSubscription->setCreatedAt(new \Datetime($postData['created_at']));
            return;
        }
    }

    private function setStatus($postData)
    {
        if (isset($postData['status'])) {
            $this->productSubscription->setStatus($postData['status']);
            return;
        }
    }
}