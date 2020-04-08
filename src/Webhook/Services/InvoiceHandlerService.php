<?php

namespace Mundipagg\Core\Webhook\Services;

use Exception;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Exceptions\NotFoundException;
use Mundipagg\Core\Kernel\Factories\ChargeFactory;
use Mundipagg\Core\Kernel\Responses\ServiceResponse;
use Mundipagg\Core\Kernel\Services\APIService;
use Mundipagg\Core\Kernel\Services\ChargeService;
use Mundipagg\Core\Webhook\Aggregates\Webhook;

class InvoiceHandlerService
{
    const COMPONENT_KERNEL = 'Kernel';
    const COMPONENT_RECURRENCE = 'Recurrence';

    /**
     * @param $component
     * @throws Exception
     */
    public function build($component)
    {
        $listInvoiceHandleService = [
            self::COMPONENT_RECURRENCE => new InvoiceRecurrenceService()
        ];

        if (empty($listInvoiceHandleService[$component])) {
            throw new Exception('Não foi encontrado o tipo de charge a ser carregado', 400);
        }

        return $listInvoiceHandleService[$component];
    }

    /**
     * @param Webhook $webhook
     * @return mixed
     * @throws InvalidParamException
     * @throws NotFoundException
     * @throws Exception
     */
    public function handle(Webhook $webhook)
    {
        $handler = $this->build($webhook->getComponent());
        return $handler->handle($webhook);
    }
}
