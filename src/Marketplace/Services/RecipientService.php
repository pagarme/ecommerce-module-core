<?php

namespace Pagarme\Core\Marketplace\Services;

use Magento\Framework\Exception\CouldNotSaveException;
use MundiAPILib\MundiAPIClient;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\ValueObjects\Id\RecipientId;
use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Marketplace\Factories\RecipientFactory;
use Pagarme\Core\Marketplace\Repositories\RecipientRepository;

class RecipientService
{
    /** @var LogService  */
    protected $logService;
    /** @var RecipientFactory */
    protected $recipientFactory;
    /** @var RecipientRepository */
    protected $recipientRepository;

    protected $config;

    public function __construct()
    {
        AbstractModuleCoreSetup::bootstrap();
        $secretKey = null;
        $this->config = AbstractModuleCoreSetup::getModuleConfiguration();

        if ($this->config->getSecretKey() != null) {
            $secretKey = $this->config->getSecretKey()->getValue();
        }

        $password = '';
        \MundiAPILib\Configuration::$basicAuthPassword = '';

        $this->mundipaggApi = new MundiAPIClient($secretKey, $password);
        $this->logService = new LogService('RecipientService', true);
        $this->recipientRepository = new RecipientRepository();
        $this->recipientFactory = new RecipientFactory();
    }

    public function saveFormRecipient($formData)
    {
        $recipientFactory = $this->recipientFactory;
        $recipient = $recipientFactory->createFromPostData($formData);

        $result = $this->createRecipientAtPagarme($recipient);
        $recipient->setPagarmeId(new RecipientId($result->id));

        return $this->saveRecipient($recipient);
    }

    public function createRecipientAtPagarme(Recipient $recipient)
    {
        $createRecipientRequest = $recipient->convertToSdkRequest();
        $recipientController = $this->mundipaggApi->getRecipients();

        try {
            $logService = $this->logService;
            $logService->info(
                'Create recipient request: ' .
                    json_encode($createRecipientRequest, JSON_PRETTY_PRINT)
            );

            $result = $recipientController->createRecipient(
                $createRecipientRequest
            );

            $logService->info(
                'Create recipient response: ' .
                    json_encode($result, JSON_PRETTY_PRINT)
            );

            return $result;
        } catch (\Exception $exception) {
            $logService->exception($exception);
            throw new \Exception(__("Can't create recipient. Please review the information and try again."));
        }
    }

    public function saveRecipient(Recipient $recipient)
    {
        $this->logService->info("Creating new recipient at platform");
        $this->recipientRepository->save($recipient);
        $this->logService->info("Recipient created: " . $recipient->getId());

        return $recipient;
    }

    /**
     * @param $sellerId
     * @throws CouldNotSaveException
     */
    public function findRecipient($sellerId)
    {
        $recipient = $this->recipientRepository->findBySellerId($sellerId);

        if(empty($recipient)) {
            $this->logService->info(
                __("The seller does not have a registered recipient.")
            );
            throw new CouldNotSaveException(
                __("Payment could not be made. Please contact the store administrator.")
            );
        }

        return $recipient;
    }
}
