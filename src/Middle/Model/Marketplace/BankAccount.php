<?php

namespace Pagarme\Core\Middle\Model\Marketplace;

use PagarmeCoreApiLib\Models\CreateBankAccountRequest;

class BankAccount
{
    private $holderName;
    private $holderType;
    private $holderDocument;
    private $bank;
    private $branchNumber;
    private $branchCheckDigit;
    private $accountNumber;
    private $type;
    private $metadata;

    public function getHolderName()
    {
        return $this->holderName;
    }

    public function getHolderType()
    {
        return $this->holderType;
    }

    public function getHolderDocument()
    {
        return $this->holderDocument;
    }

    public function getBank()
    {
        return $this->bank;
    }

    public function getBranchNumber()
    {
        return $this->branchNumber;
    }

    public function getBranchCheckDigit()
    {
        return $this->branchCheckDigit;
    }

    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }


    public function setHolderName($holderName): void
    {
        $this->holderName = $holderName;
    }

    public function setHolderType($holderType): void
    {
        $this->holderType = $holderType;
    }

    public function setHolderDocument($holderDocument): void
    {
        $this->holderDocument = $holderDocument;
    }

    public function setBank($bank): void
    {
        $this->bank = $bank;
    }

    public function setBranchNumber($branchNumber): void
    {
        $this->branchNumber = $branchNumber;
    }

    public function setBranchCheckDigit($branchCheckDigit): void
    {
        $this->branchCheckDigit = $branchCheckDigit;
    }

    public function setAccountNumber($accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function setMetadata($metadata): void
    {
        $this->metadata = $metadata;
    }

    public function convertToSdk()
    {
        return new CreateBankAccountRequest(
            $this->getHolderName(),
            $this->getHolderType(),
            $this->getHolderDocument(),
            $this->getBank(),
            $this->getBranchNumber(),
            $this->getBranchCheckDigit(),
            $this->getAccountNumber(),
            $this->getType(),
            $this->getMetadata()
        );
    }
}
