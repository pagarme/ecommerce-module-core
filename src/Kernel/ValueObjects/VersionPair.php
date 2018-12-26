<?php

namespace Mundipagg\Core\Kernel\ValueObjects;

use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;

final class VersionPair extends AbstractValueObject
{
    /** @var string */
    private $moduleVersion;
    /** @var string */
    private $coreVersion;

    public function __construct($moduleVersion, $coreVersion)
    {
        $this->setModuleVersion($moduleVersion);
        $this->setCoreVersion($coreVersion);
    }

    /**
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    /**
     * @param string $moduleVersion
     * @return VersionPair
     */
    private function setModuleVersion($moduleVersion)
    {
        $this->moduleVersion = $moduleVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getCoreVersion()
    {
        return $this->coreVersion;
    }

    /**
     * @param string $coreVersion
     * @return VersionPair
     */
    private function setCoreVersion($coreVersion)
    {
        $this->coreVersion = $coreVersion;
        return $this;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param VersionPair $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getCoreVersion() === $object->getCoreVersion() &&
            $this->getModuleVersion() === $object->getModuleVersion()
        ;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->moduleVersion = $this->getModuleVersion();
        $obj->coreVersion = $this->getCoreVersion();

        return $obj ;
    }
}