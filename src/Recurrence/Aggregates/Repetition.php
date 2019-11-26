<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Recurrence\Interfaces\RepetitionInterface;

class Repetition extends AbstractEntity implements RepetitionInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const INTERVAL_WEEK = 'week';
    const INTERVAL_MONTH = 'month';
    const INTERVAL_YEAR = 'year';

    /** @var int */
    protected $recurrencePrice;
    /** @var int */
    protected $intervalCount;
    /** @var string */
    protected $interval;
    /** @var int */
    protected $subscriptionId;
    /** @var string */
    protected $createdAt;
    /** @var string */
    protected $updatedAt;

    /**
     * @return int
     */
    public function getRecurrencePrice()
    {
        return $this->recurrencePrice;
    }

    /**
     * @param int $recurrencePrice
     * @return \Mundipagg\Core\Recurrence\Aggregates\Repetition
     */
    public function setRecurrencePrice($recurrencePrice)
    {
        if ($recurrencePrice < 0) {
            throw new Exception(
                "Recurrence price should be greater than 0: $recurrencePrice!"
            );
        }
        $this->recurrencePrice = $recurrencePrice;
        return $this;
    }

    /**
     * @return int
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @param int $subscriptionId
     * @return Repetition
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt->format(self::DATE_FORMAT);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt->format(self::DATE_FORMAT);
    }

    /**
     * @return int
     */
    public function getIntervalCount()
    {
        return $this->intervalCount;
    }

    /**
     * @param int $intervalCount
     * @return \Mundipagg\Core\Recurrence\Aggregates\Repetition
     */
    public function setIntervalCount($intervalCount)
    {
        $this->intervalCount = $intervalCount;
        return $this;
    }

    /**
     * @return string
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param int $interval
     * @return \Mundipagg\Core\Recurrence\Aggregates\Repetition
     * @throws InvalidParamException
     */
    public function setInterval($interval)
    {
        if (!in_array($interval, $this->getAvailablesInterval())) {
            throw new InvalidParamException(
                "Interval is not available!",
                $interval
            );
        }
        $this->interval = $interval;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'recurrencePrice' => $this->getRecurrencePrice(),
            'subscriptionId' => $this->getSubscriptionId(),
            'intervalCount' => $this->getIntervalCount(),
            'interval' => $this->getInterval(),
            "createdAt" => $this->getCreatedAt(),
            "updatedAt" => $this->getUpdatedAt()
        ];
    }

    public function getIntervalTypeLabel()
    {
        //@todo change to a class formater maybe
        if ($this->intervalCount > 1) {
            return $this->interval . "s";
        }
        return $this->interval;
    }

    public function getAvailablesInterval()
    {
        return [
            self::INTERVAL_WEEK,
            self::INTERVAL_MONTH,
            self::INTERVAL_YEAR
        ];
    }
}