<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Aggregates\Template\InstallmentValueObject;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

class Template extends AbstractEntity
{
    /** @var int */
    protected $id;
    /** @var bool */
    protected $isEnabled;
    /** @var boolean */
    protected $isSingle;
    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var boolean */
    protected $acceptCreditCard;
    /** @var boolean */
    protected $acceptBoleto;
    /** @var boolean */
    protected $allowInstallments;
    /** @var int */
    protected $trial;
    /** @var string */
    protected $installments;

    /** @var DueValueObject */
    protected $dueAt;
    /** @var RepetitionValueObject[] */
    protected $repetitions;

    public function __construct()
    {
        $this->isSingle =
        $this->acceptCreditCard =
        $this->acceptBoleto =
        $this->allowInstallments =
            false;

        $this->isEnabled = true;

        $this->trial =
            0;

        $this->installments = [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Template
     */
    public function setId($id)
    {
        $this->id = intval($id);
        return $this;
    }

    /**
     * @return bool
     */
    public function isSingle()
    {
        return $this->isSingle;
    }

    /**
     * @param bool $isSingle
     * @return Template
     */
    public function setIsSingle($isSingle)
    {
        $this->isSingle = boolval(intval($isSingle));
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Template
     * @throws \Exception
     */
    public function setDescription($description)
    {
        if (preg_match('/[^a-zA-Z0-9 ]+/i', $description)) {
            throw new \Exception("The field description must not use special characters.");
        }

        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAcceptCreditCard()
    {
        return $this->acceptCreditCard;
    }

    /**
     * @param bool $acceptCreditCard
     * @return Template
     */
    public function setAcceptCreditCard($acceptCreditCard)
    {
        $this->acceptCreditCard = boolval(intval($acceptCreditCard));
        return $this;
    }

    /**
     * @return bool
     */
    public function isAcceptBoleto()
    {
        return $this->acceptBoleto;
    }

    /**
     * @param bool $acceptBoleto
     * @return Template
     */
    public function setAcceptBoleto($acceptBoleto)
    {
        $this->acceptBoleto = boolval(intval($acceptBoleto));
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowInstallments()
    {
        return $this->allowInstallments;
    }

    /**
     * @param bool $allowInstallments
     * @return Template
     */
    public function setAllowInstallments($allowInstallments)
    {
        $this->allowInstallments = boolval(intval($allowInstallments));
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Template
     * @throws \Exception
     */
    public function setName($name)
    {
        if (preg_match('/[^a-zA-Z0-9 ]+/i', $name)) {
            throw new \Exception("The field name must not use special characters.");
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getTrial()
    {
        return $this->trial;
    }

    /**
     * @param int $trial
     * @return Template
     */
    public function setTrial($trial)
    {
        $this->trial = abs(intval($trial));
        return $this;
    }

    /**
     * @return array
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @param InstallmentValueObject $installment
     * @return Template
     * @throws Exception
     */
    public function addInstallment(InstallmentValueObject $installment)
    {
        foreach ($this->installments as $currentInstallment) {
            if ($installment->getValue() == $currentInstallment->getValue()) {
                throw new Exception("This installment is already added: {$installment->getValue()}");
            }
        }
        $this->installments[] = $installment;
        return $this;
    }

    public function addInstallments($installments)
    {
        if (!is_array($installments)) {
            return $this;
        }

        foreach ($installments as $installment) {
            if(empty($installment)) {
                continue;
            }
            $installmentValueObject = new InstallmentValueObject($installment);
            $this->addInstallment($installmentValueObject);
        }

        return $this;
    }

    /**
     * @return DueValueObject
     */
    public function getDueAt()
    {
        return $this->dueAt;
    }

    /**
     * @param DueValueObject $dueAt
     * @return Template
     */
    public function setDueAt($dueAt)
    {
        $this->dueAt = $dueAt;
        return $this;
    }

    /**
     * @return array
     */
    public function getRepetitions()
    {
        return $this->repetitions;
    }

    /**
     * @param RepetitionValueObject $repetitions
     * @return Template
     */
    public function addRepetition($repetition)
    {
        $this->repetitions[] = $repetition;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     * @return Template
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = boolval($isEnabled);
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
        $repetitions = [];
        foreach ($this->repetitions as $repetition) {
            $repetitions[] = [
                "cycles" => $repetition->getCycles(),
                "discountType" => $repetition->getDiscountType(),
                "discountValue" => $repetition->getDiscountValue(),
                "frequency" => $repetition->getFrequency(),
                "intervalType" => $repetition->getIntervalType()
            ];
        }

        return [
            "id" => $this->getId(),
            "isEnabled" => $this->isEnabled,
            "acceptBoleto" => $this->isAcceptBoleto(),
            "acceptCreditCard" => $this->isAcceptCreditCard(),
            "allowInstallments" => $this->isAllowInstallments(),
            "description" => $this->getDescription(),
            "id" => $this->getId(),
            "isSingle" => $this->isSingle(),
            "name" => $this->getName(),
            "trial" => $this->getTrial(),
            "installments" => json_encode($this->getInstallments()),
            "dueAt" => [
                "type" => $this->dueAt->getType(),
                "value" => $this->dueAt->getValue()
            ],
            "repetitions" => $repetitions,
        ];
    }
}