<?php

namespace Pagarme\Core\Test\Middle\Model;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Middle\Model\Customer;

class CustomerTest extends TestCase
{
    private Customer $customer;

    protected function setUp(): void
    {
        $this->customer = new Customer();
    }

    // -------------------------------------------------------------------------
    // setDocument — sanitization and uppercase
    // -------------------------------------------------------------------------

    public function testSetDocumentStoresCpfWithoutMask()
    {
        // Arrange
        $cpf = '12345678901';

        // Act
        $this->customer->setDocument($cpf);

        // Assert
        $this->assertSame('12345678901', $this->customer->getDocument());
    }

    public function testSetDocumentStripsFormattingFromCpf()
    {
        // Arrange
        $maskedCpf = '123.456.789-01';

        // Act
        $this->customer->setDocument($maskedCpf);

        // Assert
        $this->assertSame('12345678901', $this->customer->getDocument());
    }

    public function testSetDocumentStoresCnpjWithoutMask()
    {
        // Arrange
        $cnpj = '12345678000195';

        // Act
        $this->customer->setDocument($cnpj);

        // Assert
        $this->assertSame('12345678000195', $this->customer->getDocument());
    }

    public function testSetDocumentStripsFormattingFromNumericCnpj()
    {
        // Arrange
        $maskedCnpj = '12.345.678/0001-95';

        // Act
        $this->customer->setDocument($maskedCnpj);

        // Assert
        $this->assertSame('12345678000195', $this->customer->getDocument());
    }

    public function testSetDocumentUppercasesLowercaseAlphanumericCnpj()
    {
        // Arrange
        $cnpj = '1a2b3c4d000195';

        // Act
        $this->customer->setDocument($cnpj);

        // Assert
        $this->assertSame('1A2B3C4D000195', $this->customer->getDocument());
    }

    public function testSetDocumentStripsFormattingAndUppercasesAlphanumericCnpj()
    {
        // Arrange
        $maskedCnpj = '1a.2b3.c4d/0001-95';

        // Act
        $this->customer->setDocument($maskedCnpj);

        // Assert
        $this->assertSame('1A2B3C4D000195', $this->customer->getDocument());
    }

    public function testSetDocumentKeepsAlreadyCleanUppercaseAlphanumericCnpj()
    {
        // Arrange
        $cnpj = '1A2B3C4D000195';

        // Act
        $this->customer->setDocument($cnpj);

        // Assert
        $this->assertSame('1A2B3C4D000195', $this->customer->getDocument());
    }

    // -------------------------------------------------------------------------
    // getType
    // -------------------------------------------------------------------------

    public function testGetTypeReturnsIndividualForCpf()
    {
        // Arrange
        $this->customer->setDocument('12345678901');

        // Act
        $type = $this->customer->getType();

        // Assert
        $this->assertSame(Customer::INDIVIDUAL, $type);
    }

    public function testGetTypeReturnsCompanyForNumericCnpj()
    {
        // Arrange
        $this->customer->setDocument('12345678000195');

        // Act
        $type = $this->customer->getType();

        // Assert
        $this->assertSame(Customer::COMPANY, $type);
    }

    public function testGetTypeReturnsCompanyForAlphanumericCnpj()
    {
        // Arrange
        $this->customer->setDocument('1A2B3C4D000195');

        // Act
        $type = $this->customer->getType();

        // Assert
        $this->assertSame(Customer::COMPANY, $type);
    }

    // -------------------------------------------------------------------------
    // getDocumentType
    // -------------------------------------------------------------------------

    public function testGetDocumentTypeReturnsCpfForIndividual()
    {
        // Arrange
        $this->customer->setDocument('12345678901');

        // Act
        $documentType = $this->customer->getDocumentType();

        // Assert
        $this->assertSame('cpf', $documentType);
    }

    public function testGetDocumentTypeReturnsCnpjForNumericCnpj()
    {
        // Arrange
        $this->customer->setDocument('12345678000195');

        // Act
        $documentType = $this->customer->getDocumentType();

        // Assert
        $this->assertSame('cnpj', $documentType);
    }

    public function testGetDocumentTypeReturnsCnpjForAlphanumericCnpj()
    {
        // Arrange
        $this->customer->setDocument('1A2B3C4D000195');

        // Act
        $documentType = $this->customer->getDocumentType();

        // Assert
        $this->assertSame('cnpj', $documentType);
    }

    public function testGetDocumentTypeReturnsCnpjForMaskedAlphanumericCnpj()
    {
        // Arrange
        $this->customer->setDocument('1A.2B3.C4D/0001-95');

        // Act
        $documentType = $this->customer->getDocumentType();

        // Assert
        $this->assertSame('cnpj', $documentType);
    }
}
