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
use Pagarme\Core\Kernel\Log\BlurData;
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

    /** @var BlurData */
    protected $blurData;

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
        $this->blurData = new BlurData();
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
            $logObjectData = $logObject->getData();
            $this->setData($logObjectData, $this->blurData->blurData($data), 'data');
            $logObject->setData($logObjectData);
        }
        if ($data = $logObject->getData()) {
            $logObject->setData($this->blurData->blurData($data));
        }
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
}