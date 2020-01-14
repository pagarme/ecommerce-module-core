<?php

namespace Mundipagg\Core\Recurrence\Services;

use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\LogService;
use Mundipagg\Core\Kernel\ValueObjects\ChargeStatus;
use Mundipagg\Core\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use Mundipagg\Core\Recurrence\Aggregates\Invoice;
use Mundipagg\Core\Recurrence\Factories\InvoiceFactory;
use Mundipagg\Core\Recurrence\Repositories\ChargeRepository;
use Mundipagg\Core\Recurrence\ValueObjects\InvoiceStatus;

class InvoiceService
{
    private $logService;
    /**
     * @var LocalizationService
     */
    private $i18n;
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
            $logService = $this->getLogService();
            $charge = $this->getChargeRepository()
                ->findByInvoiceId($invoiceId);

            if (!$charge) {
                $message = 'Invoice not found';

                $logService->info(
                    null,
                    $message . " ID {$invoiceId} ."
                );

                //Code 404
                throw new \Exception($message, 404);
            }

            if ($charge->getStatus()->getStatus() == InvoiceStatus::canceled()->getStatus()) {
                $message = 'Invoice already canceled';

                return [
                    "message" => $message,
                    "code" => 200
                ];
            }
            $invoiceFactory = new InvoiceFactory();
            $invoice = $invoiceFactory->createFromCharge($charge);

            $return = $this->cancelInvoiceAtMundipagg($invoice);

            $charge->setStatus(ChargeStatus::canceled());

            $this->getChargeRepository()->save($charge);

            return $return;

        } catch (\Exception $exception) {
            $logService = $this->getLogService();

            $logService->info(
                null,
                $exception->getMessage()
            );

            throw $exception;
        }
    }

    public function cancelInvoiceAtMundipagg(Invoice $invoice)
    {
        $logService = $this->getLogService();
        $apiService = $this->getApiService();

        $logService->info(
            null,
            'Invoice cancel request | invoice id: ' .
            $invoice->getMundipaggId()->getValue()
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
    }

    public function getApiService()
    {
        return new APIService();
    }

    public function getLogService()
    {
        return new LogService('InvoiceService', true);
    }

    public function getChargeRepository()
    {
        return new ChargeRepository();
    }
}