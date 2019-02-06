<?php

namespace Mundipagg\Core\Hub\Commands;

use Mundipagg\ValueObject\ValueObject;

final class CommandType implements ValueObject
{
    const SANDBOX = 'Sandbox';
    const PRODUCTION = 'Production';
    const DEVELOPMENT = 'Development';

    /** @var string */
    private $value;

    public static function Sandbox()
    {
        return new self(self::SANDBOX);
    }

    public static function Production()
    {
        return new self(self::PRODUCTION);
    }

    public static function Development()
    {
        return new self(self::DEVELOPMENT);
    }

    private function __construct($value)
    {
        $this->setValue($value);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return CommandType
     */
    private function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /** @var static $object */
    public function equals($object)
    {
        return $this->value === $object->getValue();
    }
}