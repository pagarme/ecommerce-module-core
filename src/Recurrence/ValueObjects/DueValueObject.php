<?php

namespace Mundipagg\Core\Recurrence\ValueObjects;

use Exception;
use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;

class DueValueObject extends AbstractValueObject
{
    const TYPE_EXACT = "exact_day";
    const TYPE_PREPAID = "prepaid";
    const TYPE_POSTPAID = "postpaid";

    const LABEL_EXACT = "Every day";
    const LABEL_PREPAID = "Pre paid";
    const LABEL_POSTPAID = "Post paid";

    /** @var string */
    protected $type;
    /** @var int */
    protected $value;
    /** @var string */
    protected $label;

    protected function __construct($type, $value, $label)
    {
        $this->setType($type);
        $this->setValue($value);
        $this->setLabel($label);
    }

    public static function exactDay($day)
    {
        return new DueValueObject(self::TYPE_EXACT, $day, self::LABEL_EXACT);
    }

    public static function prepaid()
    {
        return new DueValueObject(self::TYPE_PREPAID, 0, self::LABEL_PREPAID);
    }

    public static function postpaid()
    {
        return new DueValueObject(self::TYPE_POSTPAID, 0, self::LABEL_POSTPAID);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return DueValueObject
     * @throws Exception
     */
    protected function setType($type)
    {
       $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return DueValueObject
     * @throws Exception
     */
    protected function setValue($value)
    {
        $intValue = intval($value);
        if ($intValue <= 0 && $this->getType() === self::TYPE_EXACT) {
            throw new Exception("Due value should be greater than 0: $value!");
        }
        $this->value = $intValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    protected function setLabel($label)
    {
        $this->label = $label;
    }

    public function getDueLabel()
    {
        return $this->getLabel();
    }

    public function getDueApiValue()
    {
       return $this->getType();
    }

    public static function getTypesArray()
    {
        return [
            ['code' => self::TYPE_EXACT, 'name' => self::LABEL_EXACT],
            ['code' => self::TYPE_PREPAID, 'name' => self::LABEL_PREPAID],
            ['code' => self::TYPE_POSTPAID, 'name' => self::LABEL_POSTPAID]
        ];
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param DueValueObject $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getType() === $object->getType() &&
            $this->getValue() === $object->getValue() &&
            $this->getLabel() === $object->getLabel();

    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
          'type' => $this->getType(),
          'value' => $this->getValue(),
          'label' => $this->getLabel(),
        ];
    }
}