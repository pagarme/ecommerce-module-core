<?php

namespace Mundipagg\Core\Kernel\Services;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Kernel\Exceptions\AbstractMundipaggCoreException;
use Mundipagg\Core\Kernel\Factories\LogObjectFactory;
use Mundipagg\Core\Kernel\Log\JsonPrettyFormatter;

final class LogService
{
    private $path;
    private $addHost;
    private $channelName;
    private $monolog;
    private $fileName;

    public function __construct($channelName, $addHost = false)
    {
        $this->channelName = $channelName;
        $this->path = AbstractModuleCoreSetup::getLogPath();

        if (is_array($this->path)) {
            $this->path = array_pop($this->path);
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

    public function info($message, $sourceObject)
    {
        $logObject = $this->prepareObject($sourceObject);

        $this->monolog->info($message, $logObject);
    }

    public function exception(AbstractMundipaggCoreException $exception)
    {
        $logObject = $this->prepareObject($exception);

        $this->monolog->error($exception->getMessage(), $logObject);
    }

    private function prepareObject($sourceObject)
    {
        $logObjectFactory = new LogObjectFactory;

        $versionService = new VersionService();

        $baseObject = $logObjectFactory->createFromLogger(
            debug_backtrace()[2],
            $sourceObject,
            $versionService->getVersionPair()
        );
        $baseObject = json_encode($baseObject);
        return json_decode($baseObject, true);
    }

    private function setFileName()
    {
        $base = 'Mundipagg_PaymentModule_' . date('Y-m-d');
        $fileName = $this->path . DIRECTORY_SEPARATOR . $base;

        if ($this->addHost) {
            $fileName .= '_' . gethostname();
        }

        $fileName .= '.log';

        $this->fileName = $fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getPath()
    {
        return $this->path;
    }
}