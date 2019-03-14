<?php

namespace Mundipagg\Core\Payment\Services\ResponseHandlers;

use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Payment\Aggregates\Order as PaymentOrder;

final class ErrorExceptionHandler extends AbstractResponseHandler
{
    /**
     * @param $error
     * @param PaymentOrder|null $paymentOrder
     * @return mixed
     */
    public function handle($error, PaymentOrder $paymentOrder = null)
    {
        $orderCode = null;
        $exceptionLogMethod = 'exception';
        if ($paymentOrder !== null) {
            $orderCode = $paymentOrder->getCode();
            $this->logService->orderInfo(
                $orderCode,
                "Failed to create order at Mundipagg!"
            );
            $exceptionLogMethod = 'orderException';
        }

        $this->logService->$exceptionLogMethod($error, $orderCode);

        $i18n = new LocalizationService();
        $frontErrorMessage = $i18n->getDashboard(
            'An error occurred when trying to create the order. Please try again. Error Reference: %s',
            $paymentOrder->getCode()
        );

        return $frontErrorMessage;
    }
}