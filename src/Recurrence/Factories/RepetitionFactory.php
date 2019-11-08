<?php


namespace Mundipagg\Core\Recurrence\Factories;


use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class RepetitionFactory implements FactoryInterface
{
    /**
     * @var Repetition
     */
    private $repetition;

    public function __construct()
    {
        $this->repetition = new Repetition();
    }
    /**
     *
     * @param array $postData
     * @return AbstractEntity
     * @throws \Exception
     */
    public function createFromPostData($postData)
    {
        if (!is_array($postData)) {
            return;
        }

        $this->setId($postData);
        $this->setSubscriptionId($postData);
        $this->setDiscount($postData);
        $this->setInterval($postData);
        $this->setCreatedAt($postData);
        $this->setUpdatedAt($postData);

        return $this->repetition;
    }

    public function setId($postData)
    {
        if (empty($postData['id'])) {
            return;
        }
        $this->repetition->setId($postData['id']);
    }

    public function setSubscriptionId($postData)
    {
        if (empty($postData['subscription_id'])) {
            return;
        }
        $this->repetition->setSubscriptionId($postData['subscription_id']);
    }

    public function setDiscount($postData)
    {
        if (empty($postData['discount_type'])) {
            return;
        }

        $discountType = $postData['discount_type'];
        $discount = DiscountValueObject::$discountType($postData['discount_value']);
        $this->repetition->setDiscount($discount);
    }

    public function setInterval($postData)
    {
        if (empty($postData['interval'])) {
            return;
        }

        $intervalType = $postData['interval'];
        $interval = IntervalValueObject::$intervalType($postData['interval_count']);
        $this->repetition->setInterval($interval);
    }

    public function setCreatedAt($postData)
    {
        if (!empty($postData['created_at'])) {
            $this->repetition->setCreatedAt(new \Datetime($postData['created_at']));
        }
    }

    public function setUpdatedAt($postData)
    {
        if (!empty($postData['updated_at'])) {
            $this->repetition->setUpdatedAt(new \Datetime($postData['updated_at']));
        }
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
}