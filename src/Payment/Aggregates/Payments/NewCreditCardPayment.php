<?php

namespace Mundipagg\Core\Payment\Aggregates\Payments;

use Mundipagg\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Mundipagg\Core\Payment\ValueObjects\CardToken;

final class NewCreditCardPayment extends AbstractCreditCardPayment
{
    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->cardToken = $this->identifier;

        return $obj;
    }

    protected function setIdentifier(AbstractCardIdentifier $identifier)
    {
        $this->identifier = $identifier;
    }

    public function setCardToken(CardToken $cardToken)
    {
        $this->setIdentifier($cardToken);
    }
}