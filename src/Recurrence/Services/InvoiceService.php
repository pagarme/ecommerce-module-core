<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;
use Mundipagg\Core\Recurrence\ValueObjects\InvoiceIdValueObject;

final class InvoiceService
{
    private $logService;
    /**
     * @var LocalizationService
     */
    private $i18n;
    private $subscriptionItems;
    private $apiService;

    public function __construct()
    {

    }

    public function getById($invoiceId)
    {

    }

    public function cancel($invoiceId)
    {
        try {
            $apiService = new APIService();

            $invoice = new Invoice();
            $invoiceId = new InvoiceIdValueObject($invoiceId);

            $invoice->setMundipaggId($invoiceId);
            $apiService->cancelInvoice($invoice);

            return [
                "message" => 'Invoice canceled successfully',
                "code" => 200
            ];
        } catch (\Exception $exception) {

            /*$message = $this->i18n->getDashboard(
                'Error on cancel invoice'
            );

            $this->logService->orderInfo(
                null,
                $message . ' - ' . $exception->getMessage()
            );*/

            return [
                "message" => $exception->getMessage(),
                "code" => 400
            ];
        }
    }

}