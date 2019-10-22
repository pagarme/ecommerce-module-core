<?php

namespace Mundipagg\Core\Test\Payments\I18N;

use Mundipagg\Core\Kernel\I18N\PTBR;
use PHPUnit\Framework\TestCase;

class PTBRTests extends TestCase
{
    /**
     * @var PTBR
     */
    private $ptbr;

    public function setUp()
    {
        $this->ptbr = new PTBR();
    }

    public function testsInfoTableResultWebHookReceived()
    {
        $this->assertEquals('Webhook recebido: %s.%s', $this->ptbr->get('Webhook received: %s.%s'));
    }

    public function testsInfoTableResulInvoicecanceled()
    {
        $this->assertEquals('Invoice cancelada: #%s', $this->ptbr->get('Invoice canceled: #%s.'));
    }
}
