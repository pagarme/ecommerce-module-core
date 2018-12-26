<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Aggregates\LogObject;
use Mundipagg\Core\Kernel\ValueObjects\VersionPair;

class LogObjectFactory
{
    /**
     * @param array $callerBacktrace
     * @param mixed $sourceObject
     * @param VersionPair $versions
     * @return LogObject
     */
    public function createFromLogger(
        $callerBacktrace,
        $sourceObject,
        VersionPair $versions
    ) {
        $baseObject = new LogObject();
        $baseObject->setVersions($versions);

        $backTrace = $callerBacktrace;
        $method = $backTrace['class'] . '::';
        $method .= $backTrace['function'];
        $method .= ':' . $backTrace['line'];
        $baseObject->setMethod($method);

        $baseObject->setData($sourceObject);

        return $baseObject;
    }

    /**
     * @param array $data
     * @return LogObject
     */
    public function createFromArray($data)
    {
        $baseObject = new LogObject();
        $baseObject->setVersions(
            new VersionPair(
                $data['versions']['moduleVersion'],
                $data['versions']['coreVersion']
            )
        );

        $baseObject->setMethod($data['method']);

        $baseObject->setData(json_decode(json_encode($data['data'])));

        return $baseObject;
    }
}