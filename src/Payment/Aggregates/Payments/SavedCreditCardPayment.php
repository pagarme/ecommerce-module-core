<?php

namespace Mundipagg\Core\Payment\Aggregates\Payments;

use Mundipagg\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Mundipagg\Core\Payment\ValueObjects\CardId;

final class SavedCreditCardPayment extends AbstractCreditCardPayment
{
    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->cardId = $this->identifier;

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
}