<?php

namespace Mundipagg\Core\Recurrence\ValueObjects;

use Exception;

class DueValueObject
{
    const TYPE_EXACT = 'X';
    const TYPE_PREPAID = 'E';
    const TYPE_POSTPAID = 'O';

    /** @var string */
    protected $type;
    /** @var int */
    protected $value;

    /**
     * @TODO to respect the immutability of a value object, this block of code
     * @TODO should be uncommented, and the system should be adapted to it.
     *
     * Examples of use:
     *
     * $foo = DueValueObject::exact(20);
     * $bar = DueValueObject::prePaid();
     * $baz = DueValueObject::postPaid();
     */
    /*protected function __construct($type, $value)
    {
        $this->setType($type);
        $this->setValue($value);
    }

    public static function exact($day)
    {
        return new DueValueObject(self::TYPE_EXACT, $day);
    }

    public static function prePaid()
    {
        return new DueValueObject(self::TYPE_PREPAID, 0);
    }

    public static function postPaid()
    {
        return new DueValueObject(self::TYPE_POSTPAID, 0);
    }*/

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
    public function setType($type)
    {
        if (!in_array($type, self::getValidTypes())) {
            throw new Exception("Invalid Due Type: $type! ");
        }
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
    public function setValue($value)
    {
        $intValue = intval($value);
        if ($intValue <= 0) {
            throw new Exception("Due value should be greater than 0: $value!");
        }
        $this->value = $intValue;
        return $this;
    }

    public function getDueLabel()
    {
        switch ($this->type) {
            case self::TYPE_EXACT:
                return "Todo dia %d";
            case self::TYPE_PREPAID:
                return "Pré-pago";
            case self::TYPE_POSTPAID:
                return "Pós-pago";
            default: return "Error: %d : " . $this->type;
        }
    }

    public function getDueApiValue()
    {
        switch ($this->type) {
            case self::TYPE_EXACT:
                return "exact_day";
            case self::TYPE_PREPAID:
                return "prepaid";
            case self::TYPE_POSTPAID:
                return "postpaid";
            default:
                return "";
        }
    }

    public static function getTypesArray()
    {
        return [
            ['code' => self::TYPE_EXACT, 'name' => "Dia exato"],
            ['code' => self::TYPE_PREPAID, 'name' => "Pré-pago"],
            ['code' => self::TYPE_POSTPAID, 'name' => "Pós-pago"]
        ];
    }

    public static function getValidTypes()
    {
        return [
            self::TYPE_EXACT,
            self::TYPE_PREPAID,
            self::TYPE_POSTPAID
        ];
    }
}