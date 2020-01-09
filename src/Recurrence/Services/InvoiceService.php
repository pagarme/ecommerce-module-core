<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;
use Mundipagg\Core\Recurrence\ValueObjects\InvoiceIdValueObject;

class InvoiceService
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
        $logService = $this->getLogService();
        try {
            $apiService = $this->getApiService();

            $invoice = new Invoice();
            $invoiceId = new InvoiceIdValueObject($invoiceId);

            $invoice->setMundipaggId($invoiceId);

            $logService->info(
                null,
                'Invoice cancel request | invoice id: ' . $invoiceId
            );

            $apiService->cancelInvoice($invoice);

            $return = [
                "message" => 'Invoice canceled successfully',
                "code" => 200
            ];

            $logService->info(
                null,
                'Invoice cancel response: ' . $return['message']
            );

            return $return;
        } catch (\Exception $exception) {
            $logService->info(
                null,
                $exception->getMessage()
            );

            return [
                "message" => $exception->getMessage(),
                "code" => 400
            ];
        }
    }

    public function getApiService()
    {
        return new APIService();
    }

    public function getLogService()
    {
        return new LogService('InvoiceService', true);
    }
}