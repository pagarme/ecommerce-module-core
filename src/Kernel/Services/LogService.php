<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Core\Kernel\Services;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Aggregates\LogObject;
use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Kernel\Factories\LogObjectFactory;
use Pagarme\Core\Kernel\Log\JsonPrettyFormatter;

/**
 * Class LogService
 */
class LogService
{
    /** @var mixed|null */
    protected $path;

    /** @var bool */
    protected $addHost;

    /** @var string */
    protected $channelName;

    /** @var Logger */
    protected $monolog;

    /** @var string */
    protected $fileName;

    /** @var int */
    protected $stackTraceDepth;

    /**
     * @param $channelName
     * @param bool $addHost
     * @throws \Exception
     */
    public function __construct(
        $channelName,
        $addHost = false
    ) {
        $this->stackTraceDepth = 2;
        $this->channelName = $channelName;
        $this->path = AbstractModuleCoreSetup::getLogPath();
        if (is_array($this->path)) {
            $this->path = array_shift($this->path);
        }
        $this->addHost = $addHost;
        $this->setFileName();
        $this->monolog = new Logger(
            $this->channelName
        );
        $handler = new StreamHandler($this->fileName, Logger::DEBUG);
        $handler->setFormatter(new JsonPrettyFormatter());
        $this->monolog->pushHandler($handler);
    }

    /**
     * @param $message
     * @param $sourceObject
     * @return void
     */
    public function info($message, $sourceObject = null)
    {
        $logObject = $this->prepareObject($sourceObject);
        $this->monolog->info($message, $logObject);
    }

    /**
     * @param \Exception $exception
     * @return void
     */
    public function exception(\Exception $exception)
    {
        $logObject = $this->prepareObject($exception);
        $code = ' | Exception code: ' . $exception->getCode();
        $this->monolog->error($exception->getMessage() . $code, $logObject);
    }

    /**
     * @param $sourceObject
     * @return mixed
     */
    protected function prepareObject($sourceObject)
    {
        $logObjectFactory = new LogObjectFactory;
        $versionService = new VersionService();
        $debugStep = $this->stackTraceDepth;
        $baseObject = $logObjectFactory->createFromLogger(
            debug_backtrace()[$debugStep],
            $sourceObject,
            $versionService->getVersionInfo()
        );
        $this->blurSensitiveData($baseObject);
        $baseObject = json_encode($baseObject);
        return json_decode($baseObject, true);
    }

    /**
     * @return void
     */
    protected function setFileName()
    {
        $base = 'Pagarme_PaymentModule_' . date('Y-m-d');
        $fileName = $this->path . DIRECTORY_SEPARATOR . $base;
        if ($this->addHost) {
            $fileName .= '_' . gethostname();
        }
        $fileName .= '.log';
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return mixed|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param LogObject $logObject
     * @return void
     */
    private function blurSensitiveData(LogObject &$logObject)
    {
        if ($data = $this->getData($logObject->getData(), 'data')) {
            foreach ($data as $method => $value) {
                $blurMethod = 'blur' . str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));
                if (method_exists($this, $blurMethod)) {
                    $data[$method] = $this->{$blurMethod}($value);
                }
            }
            $logObjectData = $logObject->getData();
            $this->setData($logObjectData, $data, 'data');
            $logObject->setData($logObjectData);
        }
    }

    /**
     * @param string $value
     * @param $delimiter
     * @return string
     */
    private function blurStringSensitiveData(string $value, $delimiter){
        $displayed = substr($value, 0, $delimiter);
        $blur = str_repeat("*", strlen($value));
        $blur = substr($blur, $delimiter);
        $result = "$displayed $blur";
        return $result;
    }

    /**
     * @param $string
     * @return string
     */
    private function blurEmailSensitiveData($string)
    {
        $displayed = substr($string, 0, 3);
        $final = substr($string, strpos($string, "@"));
        $result = "$displayed***$final";
        return $result;
    }

    /**
     * @param $haystack
     * @param $key
     * @return mixed|null
     */
    private function getData($haystack, $key)
    {
        if ($haystack instanceof \stdClass) {
            if (property_exists($haystack, $key)) {
                return $haystack->{$key};
            }
        }
        if (is_array($haystack) && isset($haystack[$key]) && $haystack[$key]) {
            return $haystack[$key];
        }
        return null;
    }

    /**
     * @param $haystack
     * @param $value
     * @param $key
     * @return void
     */
    private function setData(&$haystack, $value, $key = null)
    {
        if ($haystack instanceof \stdClass && $key) {
            $haystack->{$key} = $value;
            return;
        }
        if (is_array($haystack) && $key) {
            $haystack[$key] = $value;
            return;
        }
        $haystack = $value;
    }

    /**
     * @param array $customer
     * @return array
     */
    private function blurCustomer(array $customer)
    {
        foreach ($customer as $key => $value) {
            switch ($key) {
                case 'name' :
                    $value = $this->blurStringSensitiveData($value, 5);
                    break;
                case 'email' :
                    $value = $this->blurEmailSensitiveData($value);
                    break;
                case 'document' :
                    $value = preg_replace('/\B[^@.]/', '*', $value);
                    break;
                case 'address' :
                    $value = $this->blurAddress($value);
                    break;
                default :
                    $value = '***********';
            }
            $customer[$key] = $value;
        }
        return $customer;
    }

    /**
     * @param array $address
     * @return array
     */
    private function blurAddress(array $address)
    {
        foreach ($address as $key => $value) {
            switch ($key) {
                case 'street' :
                case 'line_1' :
                    $value = $this->blurStringSensitiveData($value, 8);
                    break;
                case 'zip_code' :
                    $value = $this->blurStringSensitiveData($value, 5);
                    break;
                default :
                    $value = '***********';
            }
            $address[$key] = $value;
        }
        return $address;
    }

    /**
     * @param array $shipping
     * @return array
     */
    private function blurShipping(array $shipping)
    {
        foreach ($shipping as $key => $value) {
            switch ($key) {
                case 'recipient_name' :
                    $value = $this->blurStringSensitiveData($value, 5);
                    break;
                case 'address' :
                    $value = $this->blurAddress($value);
                    break;
                default :
                    $value = '***********';
            }
            $address[$key] = $value;
        }
        return $shipping;
    }

    /**
     * @param array $payments
     * @return array
     */
    private function blurPayments(array $payments)
    {
        foreach ($payments as &$payment) {
            foreach ($payment as $method => $value) {
                $blurMethod = 'blur' . str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));
                if (method_exists($this, $blurMethod)) {
                    $payment[$method] = $this->{$blurMethod}($value);
                }
            }
        }
        return $payments;
    }

    /**
     * @param array $creditCard
     * @return array
     */
    private function blurCreditCard(array $creditCard)
    {
        foreach ($creditCard as $method => $value) {
            $blurMethod = 'blur' . str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));
            if (method_exists($this, $blurMethod)) {
                $creditCard[$method] = $this->{$blurMethod}($value);
            }
        }
        return $creditCard;
    }

    /**
     * @param array $payments
     * @return array
     */
    private function blurLastTransaction(array $lastTransaction)
    {
        foreach ($lastTransaction as $method => &$value) {
            $blurMethod = 'blur' . str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));
            if (method_exists($this, $blurMethod)) {
                $lastTransaction[$method] = $this->{$blurMethod}($value);
            }
        }
        return $lastTransaction;
    }

    /**
     * @param array $card
     * @return array
     */
    private function blurCard(array $card)
    {
        foreach ($card as $method => $value) {
            $blurMethod = 'blur' . str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));
            if (method_exists($this, $blurMethod)) {
                $card[$method] = $this->{$blurMethod}($value);
            }
        }
        return $card;
    }

    /**
     * @param string $card
     * @return string
     */
    private function blurHolderName(string $holderName)
    {
        return preg_replace('/^.{8}/', '$1**', $holderName);
    }

    /**
     * @param array $card
     * @return array
     */
    private function blurBillingAddress(array $billingAddress)
    {
        return $this->blurAddress($billingAddress);
    }

    /**
     * @param array $charges
     * @return array
     */
    private function blurCharges(array $charges)
    {
        foreach ($charges as &$charge) {
            foreach ($charge as $method => &$value) {
                $blurMethod = 'blur' . str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));
                if (method_exists($this, $blurMethod)) {
                    $charge[$method] = $this->{$blurMethod}($value);
                }
            }
        }
        return $charges;
    }
}
