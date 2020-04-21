<?php

namespace Mundipagg\Core\Payment\Aggregates\Payments;

use MundiAPILib\Models\CreateCreditCardPaymentRequest;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Mundipagg\Core\Payment\ValueObjects\CardToken;
use Mundipagg\Core\Payment\ValueObjects\PaymentMethod;

class NewDebitCardPayment extends NewCreditCardPayment
{
    static public function getBaseCode()
    {
        return PaymentMethod::debitCard()->getMethod();
    }
}