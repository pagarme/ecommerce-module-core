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

        $this->setExternalId($postData);
        $this->setName($postData);
        $this->setEmail($postData);
        $this->setDocumentType($postData);
        $this->setDocument($postData);
        $this->setType($postData);
        $this->setHolderName($postData);
        $this->setHolderDocument($postData);
        $this->setHolderType($postData);
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

    private function setExternalId($postData)
    {
        if (array_key_exists('external_id', $postData)) {
            $this->recipient->setExternalId($postData['external_id']);
            return;
        }
    }

    private function setName($postData)
    {
        if (array_key_exists('name', $postData)) {
            $this->recipient->setName($postData['name']);
            return;
        }
    }

    private function setEmail($postData)
    {
        if (array_key_exists('email', $postData)) {
            $this->recipient->setEmail($postData['email']);
            return;
        }
    }

    private function setDocumentType($postData)
    {
        if (array_key_exists('document_type', $postData)) {
            $this->recipient->setDocumentType($postData['document_type']);
            return;
        }
    }

    private function setDocument($postData)
    {
        if (array_key_exists('document', $postData)) {
            $this->recipient->setDocument($postData['document']);
            return;
        }
    }

    private function setType($postData)
    {
        if (array_key_exists('type', $postData)) {
            $this->recipient->setType($postData['type']);
            return;
        }
    }

    private function setHolderName($postData)
    {
        if (array_key_exists('holder_name', $postData)) {
            $this->recipient->setHolderName($postData['holder_name']);
            return;
        }
    }

    private function setHolderDocument($postData)
    {
        if (array_key_exists('holder_document', $postData)) {
            $this->recipient->setHolderDocument($postData['holder_document']);
            return;
        }
    }

    private function setHolderType($postData)
    {
        if (array_key_exists('holder_type', $postData)) {
            $this->recipient->setHolderType($postData['holder_type']);
            return;
        }
    }

    private function setBank($postData)
    {
        if (array_key_exists('bank', $postData)) {
            $this->recipient->setBank($postData['bank']);
            return;
        }
    }

    private function setBranchNumber($postData)
    {
        if (array_key_exists('branch_number', $postData)) {
            $this->recipient->setBranchNumber($postData['branch_number']);
            return;
        }
    }

    private function setBranchCheckDigit($postData)
    {
        if (array_key_exists('branch_check_digit', $postData)) {
            $this->recipient->setBranchCheckDigit($postData['branch_check_digit']);
            return;
        }
    }

    private function setAccountNumber($postData)
    {
        if (array_key_exists('account_number', $postData)) {
            $this->recipient->setAccountNumber($postData['account_number']);
            return;
        }
    }

    private function setAccountCheckDigit($postData)
    {
        if (array_key_exists('account_check_digit', $postData)) {
            $this->recipient->setAccountCheckDigit($postData['account_check_digit']);
            return;
        }
    }

    private function setAccountType($postData)
    {
        if (array_key_exists('account_type', $postData)) {
            $this->recipient->setAccountType($postData['account_type']);
            return;
        }
    }

    private function setTransferEnabled($postData)
    {
        if (array_key_exists('transfer_enabled', $postData)) {
            $this->recipient->setTransferEnabled($postData['transfer_enabled']);
            return;
        }
    }

    private function setTransferInterval($postData)
    {
        if (array_key_exists('transfer_interval', $postData)) {
            $this->recipient->setTransferInterval($postData['transfer_interval']);
            return;
        }
    }

    private function setTransferDay($postData)
    {
        if (array_key_exists('transfer_day', $postData)) {
            $this->recipient->setTransferDay($postData['transfer_day']);
            return;
        }
    }
}
