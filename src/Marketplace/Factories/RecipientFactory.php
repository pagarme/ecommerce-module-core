<?php

namespace Pagarme\Core\Marketplace\Factories;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Marketplace\Aggregates\Recipient;

class RecipientFactory implements FactoryInterface
{
    /**
     * @var Recipient
     */
    protected $recipient;

    public function __construct()
    {
        $this->recipient = new Recipient();
    }

    public function createFromPostData($postData)
    {
        if (!is_array($postData)) {
            return;
        }

        $this->setInternalId($postData);
        $this->setName($postData);
        $this->setEmail($postData);
        $this->setDocumentType($postData);
        $this->setDocument($postData);
        $this->setHolderName($postData);
        $this->setHolderDocument($postData);
        $this->setBank($postData);
        $this->setBranchNumber($postData);
        $this->setBranchCheckDigit($postData);
        $this->setAccountNumber($postData);
        $this->setAccountCheckDigit($postData);
        $this->setAccountType($postData);
        $this->setTransferEnabled($postData);
        $this->setTransferInterval($postData);
        $this->setTransferDay($postData);

        return $this->recipient;
    }

    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }

    private function setInternalId($postData)
    {
        if (!empty($postData['internal_id'])) {
            $this->recipient->setInternalId($postData['internal_id']);
            return;
        }
    }

    private function setName($postData)
    {
        if (!empty($postData['name'])) {
            $this->recipient->setName($postData['name']);
            return;
        }
    }

    private function setEmail($postData)
    {
        if (!empty($postData['email'])) {
            $this->recipient->setEmail($postData['email']);
            return;
        }
    }

    private function setDocumentType($postData)
    {
        if (!empty($postData['document_type'])) {
            $this->recipient->setDocumentType($postData['document_type']);
            return;
        }
    }

    private function setDocument($postData)
    {
        if (!empty($postData['document'])) {
            $this->recipient->setDocument($postData['document']);
            return;
        }
    }

    private function setHolderName($postData)
    {
        if (!empty($postData['holder_name'])) {
            $this->recipient->setHolderName($postData['holder_name']);
            return;
        }
    }

    private function setHolderDocument($postData)
    {
        if (!empty($postData['holder_document'])) {
            $this->recipient->setHolderDocument($postData['holder_document']);
            return;
        }
    }

    private function setBank($postData)
    {
        if (!empty($postData['bank'])) {
            $this->recipient->setBank($postData['bank']);
            return;
        }
    }

    private function setBranchNumber($postData)
    {
        if (!empty($postData['branch_number'])) {
            $this->recipient->setBranchNumber($postData['branch_number']);
            return;
        }
    }

    private function setBranchCheckDigit($postData)
    {
        if (!empty($postData['branch_check_digit'])) {
            $this->recipient->setBranchCheckDigit($postData['branch_check_digit']);
            return;
        }
    }

    private function setAccountNumber($postData)
    {
        if (!empty($postData['account_number'])) {
            $this->recipient->setAccountNumber($postData['account_number']);
            return;
        }
    }

    private function setAccountCheckDigit($postData)
    {
        if (!empty($postData['account_check_digit'])) {
            $this->recipient->setAccountCheckDigit($postData['account_check_digit']);
            return;
        }
    }

    private function setAccountType($postData)
    {
        if (!empty($postData['account_type'])) {
            $this->recipient->setAccountType($postData['account_type']);
            return;
        }
    }

    private function setTransferEnabled($postData)
    {
        if (!empty($postData['transfer_enabled'])) {
            $this->recipient->setTransferEnabled($postData['transfer_enabled']);
            return;
        }
    }

    private function setTransferInterval($postData)
    {
        if (!empty($postData['transfer_interval'])) {
            $this->recipient->setTransferInterval($postData['transfer_interval']);
            return;
        }
    }

    private function setTransferDay($postData)
    {
        if (!empty($postData['transfer_day'])) {
            $this->recipient->setTransferDay($postData['transfer_day']);
            return;
        }
    }
}
