<?php

namespace Mundipagg\Core\Test\Recurrence\ValueObjects;

use Mundipagg\Core\Recurrence\ValueObjects\DueValueObject;
use PHPUnit\Framework\TestCase;

class DueValueObjectTest extends TestCase
{
    public function testDueValueObjectExactDay()
    {
        $dueValueObject = DueValueObject::exactDay(10);
        $this->assertInstanceOf(DueValueObject::class, $dueValueObject);
    }

    public function testDueValueObjectPrepaid()
    {
        $object = DueValueObject::prepaid();
        $this->assertInstanceOf(DueValueObject::class, $object);
    }

    public function testDueValueObjectPostpaid()
    {
        $object = DueValueObject::postpaid();
        $this->assertInstanceOf(DueValueObject::class, $object);
    }

    public function testDueValueObjectValue()
    {
        $object = DueValueObject::postpaid();
        $this->assertEquals(0, $object->getValue());
    }

    public function testDueValueObjectLabel()
    {
        $object = DueValueObject::postpaid();
        $this->assertEquals('Post paid', $object->getLabel());
    }

    public function testDueValueObjectTypesArray()
    {
        $object = DueValueObject::getTypesArray();
        $this->assertInternalType('array', $object);
    }

    public function testDueValueObjectEquals()
    {
        $object = DueValueObject::postpaid();
        $this->assertEquals(true, $object->equals(DueValueObject::postpaid()));
    }

    public function testDueValueObjectJsonSerialize()
    {
        $object = DueValueObject::postpaid()->jsonSerialize();
        $this->assertInternalType('array', $object);
    }
}
