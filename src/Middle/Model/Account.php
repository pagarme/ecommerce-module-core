<?php
namespace Pagarme\Core\Middle\Model;

use PagarmeCoreApiLib\Models\GetAccountResponse;
use InvalidArgumentException;

class Account
{
    private $accountId;

    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }
    public function convertToSdk()
    {
        $accountResponse = new GetAccountResponse();
        $accountResponse->accountId = $this->getAccountId();
        return $accountResponse;
    }
}
