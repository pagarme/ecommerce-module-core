<?php

namespace Mundipagg\Core\Payment\Aggregates\Payments;

use MundiAPILib\Models\CreateCreditCardPaymentRequest;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Mundipagg\Core\Payment\ValueObjects\CardId;
use Mundipagg\Core\Payment\ValueObjects\PaymentMethod;

final class SavedVoucherCardPayment extends AbstractCreditCardPayment
{
    /** @var CustomerId */
    private $owner;
    private $cvv;

    /**
     * @return CustomerId
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param CustomerId $owner
     */
    public function setOwner(CustomerId $owner)
    {
        $this->owner = $owner;
    }

    public function setCvv($cvv)
    {
        $this->cvv = $cvv;
    }

    public function getCvv()
    {
        return $this->cvv;
    }

    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->cardId = $this->identifier;
        $obj->owner = $this->owner;

        return $obj;
    }

    public function setIdentifier(AbstractCardIdentifier $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param CardId $identifier
     */
    public function setCardId(CardId $cardId)
    {
        $this->setIdentifier($cardId);
    }

    /**
     * @return CreateCreditCardPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $paymentRequest = parent::convertToPrimitivePaymentRequest();

        $card = new \StdClass();
        $card->cvv = $this->getCvv();

        $paymentRequest->card = $card;
        $paymentRequest->cardId = $this->getIdentifier()->getValue();

        return $paymentRequest;
    }

    static public function getBaseCode()
    {
        return PaymentMethod::voucher()->getMethod();
    }
}
