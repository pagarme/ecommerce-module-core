<?php

namespace Pagarme\Core\Test\Marketplace\Aggregates;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface;

class RecipientTest extends TestCase
{
    /**
     * @dataProvider statusDataProvider
     */
    public function testParseStatus($status, $kycStatus, $expectedStatus)
    {
        $result = Recipient::parseStatus($status, $kycStatus);
        $this->assertEquals($expectedStatus, $result);
    }

    public function statusDataProvider()
    {
        return [
            ["registration", "pending", RecipientInterface::REGISTERED],
            ["affiliation", "partially_denied", RecipientInterface::VALIDATION_REQUESTED],
            ["affiliation", "pending", RecipientInterface::WAITING_FOR_ANALYSIS],
            ["active", "approved", RecipientInterface::ACTIVE],
            ["registration", "denied", RecipientInterface::DISAPPROVED],
            ["suspended", "", RecipientInterface::SUSPENDED],
            ["blocked", "", RecipientInterface::BLOCKED],
            ["inactive", "", RecipientInterface::INACTIVE],
        ];
    }
}
