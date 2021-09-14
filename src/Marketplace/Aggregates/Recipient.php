<?php

namespace Pagarme\Core\Marketplace\Aggregates;

use MundiAPILib\Models\CreateBankAccountRequest;
use MundiAPILib\Models\CreateRecipientRequest;
use MundiAPILib\Models\CreateTransferRequest;
use MundiAPILib\Models\CreateTransferSettingsRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;

class Recipient extends AbstractEntity implements RecipientInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var string */
    private $externalId = '';
    /** @var string */
    private $name = '';
    /** @var string */
    private $email = '';
    /** @var string */
    private $documentType = '';
    /** @var string */
    private $document = '';
    /** @var string */
    private $type = '';
    /** @var string */
    private $holderName = '';
    /** @var string */
    private $holderDocument = '';
    /** @var string */
    private $holderType = '';
    /** @var string */
    private $bank = '';
    /** @var string */
    private $branchNumber = '';
    /** @var string */
    private $branchCheckDigit = '';
    /** @var string */
    private $accountNumber = '';
    /** @var string */
    private $accountCheckDigit = '';
    /** @var string */
    private $accountType = '';
    /** @var bool */
    private $transferEnabled = false;
    /** @var string */
    private $transferInterval = '';
    /** @var int */
    private $transferDay = 0;
    /** @var string */
    private $createdAt;
    /** @var string */
    private $updatedAt;

    /** @var LocalizationService */
    protected $i18n;

    public function __construct()
    {
        $this->i18n = new LocalizationService();
    }

    /**
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     * @return Recipient
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
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
     * @return Recipient
     * @throws InvalidParamException
     */
    public function setName($name)
    {
        if (empty($name)) {
            $inputName = $this->i18n->getDashboard('name');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Recipient
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * @param string $documentType
     * @return Recipient
     * @throws InvalidParamException
     */
    public function setDocumentType($documentType)
    {
        if (empty($documentType)) {
            $inputName = $this->i18n->getDashboard('documentType');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->documentType = $documentType;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param string $document
     * @return Recipient
     * @throws InvalidParamException
     */
    public function setDocument($document)
    {
        if (empty($document)) {
            $inputName = $this->i18n->getDashboard('document');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->document = $document;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Recipient
     * @throws InvalidParamException
     */
    public function setType($type)
    {
        if (empty($type)) {
            $inputName = $this->i18n->getDashboard('type');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getHolderName()
    {
        return $this->holderName;
    }

    /**
     * @param string $holderName
     * @return Recipient
     * @throws InvalidParamException
     */
    public function setHolderName($holderName)
    {
        if (empty($holderName)) {
            $inputName = $this->i18n->getDashboard('holderName');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->holderName = $holderName;

        return $this;
    }

    /**
     * @return string
     */
    public function getHolderType()
    {
        return $this->holderType;
    }

    /**
     * @param string $holderType
     * @return Recipient
     * @throws InvalidParamException
     */
    public function setHolderType($holderType)
    {
        if (empty($holderType)) {
            $inputName = $this->i18n->getDashboard('holderType');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->holderType = $holderType;

        return $this;
    }

    /**
     * @return string
     */
    public function getHolderDocument()
    {
        return $this->holderDocument;
    }

    /**
     * @param string $holderDocument
     * @return Recipient
     * @throws InvalidParamException
     */
    public function setHolderDocument($holderDocument)
    {
        if (empty($holderDocument)) {
            $inputName = $this->i18n->getDashboard('holderDocument');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->holderDocument = $holderDocument;

        return $this;
    }

    /**
     * @return string
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param string $bank
     * @return Recipient
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchNumber()
    {
        return $this->branchNumber;
    }

    /**
     * @param string $branchNumber
     * @return Recipient
     */
    public function setBranchNumber($branchNumber)
    {
        $this->branchNumber = $branchNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchCheckDigit()
    {
        return $this->branchCheckDigit;
    }

    /**
     * @param string $branchCkeckDigit
     * @return Recipient
     */
    public function setBranchCheckDigit($branchCkeckDigit)
    {
        $this->branchCheckDigit = $branchCkeckDigit;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     * @return Recipient
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountCheckDigit()
    {
        return $this->accountCheckDigit;
    }

    /**
     * @param string $accountCheckDigit
     * @return Recipient
     */
    public function setAccountCheckDigit($accountCheckDigit)
    {
        $this->accountCheckDigit = $accountCheckDigit;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param string $accountType
     * @return Recipient
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
        return $this;
    }

    /**
     * @return bool
     */
    public function getTransferEnabled()
    {
        return $this->transferEnabled;
    }

    /**
     * @param string $transferEnabled
     * @return Recipient
     */
    public function setTransferEnabled($transferEnabled)
    {
        $this->transferEnabled = ($transferEnabled == 0) ? false : true;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransferInterval()
    {
        return $this->transferInterval;
    }

    /**
     * @param string $transferInterval
     * @return Recipient
     */
    public function setTransferInterval($transferInterval)
    {
        $this->transferInterval = $transferInterval;
        return $this;
    }

    /**
     * @return int
     */
    public function getTransferDay()
    {
        return $this->transferDay;
    }

    /**
     * @param int $transferDay
     * @return Recipient
     */
    public function setTransferDay($transferDay)
    {
        $this->transferDay = $transferDay;
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
     * @return ProductSubscription
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt->format(self::DATE_FORMAT);
        return $this;
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
     * @return ProductSubscription
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt->format(self::DATE_FORMAT);
        return $this;
    }

    public function convertToSdkRequest()
    {
        $recipientRequest = new CreateRecipientRequest();

        $recipientRequest->name = $this->getName();
        $recipientRequest->email = $this->getEmail();
        $recipientRequest->document = $this->getDocument();
        $recipientRequest->type = $this->getType();

        $recipientRequest->defaultBankAccount = new CreateBankAccountRequest();
        $recipientRequest->defaultBankAccount
            ->holderName = $this->getHolderName();
        $recipientRequest->defaultBankAccount
            ->holderType = $this->getHolderType();
        $recipientRequest->defaultBankAccount
            ->holderDocument = $this->getHolderDocument();
        $recipientRequest->defaultBankAccount
            ->bank = $this->getBank();
        $recipientRequest->defaultBankAccount
            ->branchNumber = $this->getBranchNumber();
        $recipientRequest->defaultBankAccount
            ->branchCheckDigit = $this->getBranchCheckDigit();
        $recipientRequest->defaultBankAccount
            ->accountNumber = $this->getAccountNumber();
        $recipientRequest->defaultBankAccount
            ->accountCheckDigit = $this->getAccountCheckDigit();
        $recipientRequest->defaultBankAccount
            ->type = $this->getAccountType();

        $recipientRequest->transferSettings = new CreateTransferSettingsRequest();
        $recipientRequest->transferSettings
            ->transferEnabled = $this->getTransferEnabled();
        $recipientRequest->transferSettings
            ->transferInterval = $this->getTransferInterval();
        $recipientRequest->transferSettings
            ->transferDay = $this->getTransferDay();

        return $recipientRequest;
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
        $obj = new \stdClass();

        $obj->externalId = $this->getExternalId();
        $obj->name = $this->getName();
        $obj->email = $this->getEmail();
        $obj->document = $this->getDocument();
        $obj->holderName = $this->getHolderName();
        $obj->holderDocument = $this->getHolderDocument();
        $obj->bank = $this->getBank();
        $obj->branchNumber = $this->getBranchNumber();
        $obj->branchCheckDigit = $this->getBranchCheckDigit();
        $obj->accountNumber = $this->getAccountNumber();
        $obj->accountCheckDigit = $this->getAccountCheckDigit();
        $obj->accountType = $this->getAccountType();
        $obj->transferEnabled = $this->getTransferEnabled();
        $obj->transferInterval = $this->getTransferInterval();
        $obj->transferDay = $this->getTransferDay();
        $obj->createdAt = $this->getCreatedAt();
        $obj->updatedAt = $this->getUpdatedAt();

        return $obj;
    }
}
