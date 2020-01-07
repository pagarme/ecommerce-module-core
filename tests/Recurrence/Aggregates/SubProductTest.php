<?php

namespace Mundipagg\Core\Test\Recurrence\Aggregates;

use Mundipagg\Core\Recurrence\Aggregates\Increment;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\ValueObjects\PricingSchemeValueObject;
use PHPUnit\Framework\TestCase;

class SubProductTest extends TestCase
{
    private $subProduct;

    protected function setUp()
    {
        $this->subProduct = new SubProduct();
    }

    public function testJsonSerializeShouldReturnAnInstanceOfStdClass()
    {
        $this->assertNotEmpty($this->subProduct->jsonSerialize());
    }

    public function testJsonSerializeShouldSetAllProperties()
    {
        $id = '1';
        $name = "Product Name";
        $description = "Product Description";
        $productId = 1234;
        $productRecurrenceId = 5678;
        $recurrenceType = 'subscription';
        $pricingScheme = PricingSchemeValueObject::unit(2);
        $quantity = 5;
        $cycles = 10;
        $createdAt = new \Datetime();
        $updatedAt = new \Datetime();
        $increment = new Increment();

        $selectedRepetition = new Repetition();

        $this->subProduct->setId($id);
        $this->assertEquals($this->subProduct->getId(), $id);

        $this->subProduct->setName($name);
        $this->assertEquals($this->subProduct->getName(), $name);

        $this->subProduct->setDescription($description);
        $this->assertEquals($this->subProduct->getDescription(), $description);

        $this->subProduct->setProductRecurrenceId($productRecurrenceId);
        $this->assertEquals($this->subProduct->getProductRecurrenceId(), $productRecurrenceId);

        $this->subProduct->setProductId($productId);
        $this->assertEquals($this->subProduct->getProductId(), $productId);

        $this->subProduct->setQuantity($quantity);
        $this->assertEquals($this->subProduct->getQuantity(), $quantity);

        $this->subProduct->setCycles($cycles);
        $this->assertEquals($this->subProduct->getCycles(), $cycles);

        $this->subProduct->setPricingScheme($pricingScheme);
        $this->assertEquals($this->subProduct->getPricingScheme(), $pricingScheme);

        $this->subProduct->setRecurrenceType($recurrenceType);
        $this->assertEquals($this->subProduct->getRecurrenceType(), $recurrenceType);

        $this->subProduct->setCreatedAt($createdAt);
        $this->assertInternalType('string', $this->subProduct->getCreatedAt());

        $this->subProduct->setUpdatedAt($updatedAt);
        $this->assertInternalType('string', $this->subProduct->getUpdatedAt());

        $this->subProduct->setSelectedRepetition($selectedRepetition);
        $this->assertInstanceOf(Repetition::class, $this->subProduct->getSelectedRepetition());

        $this->subProduct->setIncrement($increment);
        $this->assertInstanceOf(Increment::class, $this->subProduct->getIncrement());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  The field name must not use special characters.
     */
    public function testShouldNotAddANameWithSpecialCharacters()
    {
        $this->subProduct->setName("á$$#@");
    }

    public function testShouldSetCorrectNAme()
    {
        $this->subProduct->setName("Product Name");
        $this->assertEquals("Product Name", $this->subProduct->getName());
    }

    public function testShouldReturnAStdClassWhenCallTheMethodConvertToSdkRequest()
    {
        $this->subProduct->setIncrement(new Increment());
        $this->assertInstanceOf(\stdClass::class, $this->subProduct->convertToSdkRequest());
    }
}