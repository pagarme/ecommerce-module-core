<?php

namespace Pagarme\Core\Kernel\Factories;

use Pagarme\Core\Kernel\Aggregates\LogObject;
use Pagarme\Core\Kernel\ValueObjects\VersionInfo;

class LogObjectFactory
{
    /**
     *
     * @param  array       $callerBacktrace
     * @param  mixed       $baseSourceObject
     * @param  VersionInfo $versions
     * @return LogObject
     */
    public function createFromLogger(
        $callerBacktrace,
        $baseSourceObject,
        VersionInfo $versions
    ) {
        $baseObject = new LogObject();
        $baseObject->setVersions($versions);

        $backTrace = $callerBacktrace;
        $method = $backTrace['file'] . ':';
        $method .= $backTrace['line'] . ' -> ';
        $method .= $backTrace['class'] . '::';
        $method .= $backTrace['function'];
        $baseObject->setMethod($method);

        $sourceObject = [];
        if ($baseSourceObject !== null) {
            $sourceObject = $baseSourceObject;
        }
        $baseObject->setData($sourceObject);

        return $baseObject;
    }

    /**
     *
     * @param  array $data
     * @return LogObject
     */
    public function createFromArray($data)
    {
        $baseObject = new LogObject();
        $baseObject->setVersions(
            new VersionInfo(
                $this->findKey($data, 'moduleVersion') ?? '',
                $this->findKey($data, 'coreVersion') ?? '',
                $this->findKey($data, 'platformVersion') ?? ''
            )
        );
        $baseObject->setMethod($this->findKey($data, 'method') ?? '');
        $baseObject->setData(json_decode(json_encode( $this->findKey($data, 'data') ?? '')));
        return $baseObject;
    }

    /**
     * @param $array
     * @param $keySearch
     * @return false|mixed
     */
    public function findKey($array, $keySearch)
    {
        foreach ($array as $key => $item) {
            if ($key == $keySearch) {
                return $array[$keySearch];
            } elseif (is_array($item) && $this->findKey($item, $keySearch)) {
                return $item[$keySearch];
            }
        }
        return false;
    }
}