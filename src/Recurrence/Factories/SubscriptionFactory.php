<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\ValueObjects\Id\PlanId;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class SubscriptionFactory implements FactoryInterface
{
    /**
     * @param array $postData
     * @return AbstractEntity|Subscription
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $subscription = new Subscription();

        $subscription->setSubscriptionId(new SubscriptionId($postData['id']));
        $subscription->setCode($postData['code']);
        $subscription->setStatus(SubscriptionStatus::{$postData['status']}());
        $subscription->setInstallments($postData['installments']);
        $subscription->setPaymentMethod(PaymentMethod::{$postData['payment_method']}());
        $subscription->setRecurrenceType('mock depois tirar');
        $subscription->setIntervalType(IntervalValueObject::{$postData['interval']}($postData['interval_count']));
        //talvez tirar esse item, já que existe dentro de interval type
        $subscription->setIntervalCount($postData['interval_count']);

        $subscription->setMundipaggId(new SubscriptionId($postData['id']));

        if (isset($postData['plan_id'])) {
            $subscription->setPlanId(new PlanId($postData['plan_id']));
        }

        return $subscription;
    }

    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }
}
