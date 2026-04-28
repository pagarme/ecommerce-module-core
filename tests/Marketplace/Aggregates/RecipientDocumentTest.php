<?php

namespace Pagarme\Core\Test\Marketplace\Aggregates;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Marketplace\Aggregates\Recipient;

class RecipientDocumentTest extends TestCase
{
    private Recipient $recipient;

    protected function setUp(): void
    {
        $this->recipient = new Recipient();
    }

    // -------------------------------------------------------------------------
    // setDocument — happy paths
    // -------------------------------------------------------------------------

    public function testSetDocumentStoresNumericCpf()
    {
        // Arrange
        $cpf = '12345678901';

        // Act
        $this->recipient->setDocument($cpf);

        // Assert
        $this->assertSame('12345678901', $this->recipient->getDocument());
    }

    public function testSetDocumentStoresNumericCnpj()
    {
        // Arrange
        $cnpj = '12345678000195';

        // Act
        $this->recipient->setDocument($cnpj);

        // Assert
        $this->assertSame('12345678000195', $this->recipient->getDocument());
    }

    public function testSetDocumentUppercasesAlphanumericCnpj()
    {
        // Arrange
        $cnpj = '1a2b3c4d000195';

        // Act
        $this->recipient->setDocument($cnpj);

        // Assert
        $this->assertSame('1A2B3C4D000195', $this->recipient->getDocument());
    }

    public function testSetDocumentStripsFormattingFromAlphanumericCnpj()
    {
        // Arrange
        $maskedCnpj = '1A.2B3.C4D/0001-95';

        // Act
        $this->recipient->setDocument($maskedCnpj);

        // Assert
        $this->assertSame('1A2B3C4D000195', $this->recipient->getDocument());
    }

    public function testSetDocumentStripsFormattingFromNumericCnpj()
    {
        // Arrange
        $maskedCnpj = '12.345.678/0001-95';

        // Act
        $this->recipient->setDocument($maskedCnpj);

        // Assert
        $this->assertSame('12345678000195', $this->recipient->getDocument());
    }

    public function testSetDocumentStripsFormattingFromCpf()
    {
        // Arrange
        $maskedCpf = '123.456.789-01';

        // Act
        $this->recipient->setDocument($maskedCpf);

        // Assert
        $this->assertSame('12345678901', $this->recipient->getDocument());
    }

    public function testSetDocumentKeepsAlreadyUppercaseAlphanumericCnpj()
    {
        // Arrange
        $cnpj = '1A2B3C4D000195';

        // Act
        $this->recipient->setDocument($cnpj);

        // Assert
        $this->assertSame('1A2B3C4D000195', $this->recipient->getDocument());
    }

    // -------------------------------------------------------------------------
    // setDocument — unhappy paths
    // -------------------------------------------------------------------------

    public function testSetDocumentThrowsOnEmptyString()
    {
        // Arrange
        $this->expectException(\Throwable::class);

        // Act
        $this->recipient->setDocument('');
    }

    public function testSetDocumentThrowsWhenOnlyFormattingChars()
    {
        // Arrange
        $this->expectException(\Throwable::class);

        // Act
        $this->recipient->setDocument('...-/');
    }

    // -------------------------------------------------------------------------
    // setHolderDocument — happy paths
    // -------------------------------------------------------------------------

    public function testSetHolderDocumentStoresNumericCpf()
    {
        // Arrange
        $cpf = '12345678901';

        // Act
        $this->recipient->setHolderDocument($cpf);

        // Assert
        $this->assertSame('12345678901', $this->recipient->getHolderDocument());
    }

    public function testSetHolderDocumentStoresNumericCnpj()
    {
        // Arrange
        $cnpj = '12345678000195';

        // Act
        $this->recipient->setHolderDocument($cnpj);

        // Assert
        $this->assertSame('12345678000195', $this->recipient->getHolderDocument());
    }

    public function testSetHolderDocumentUppercasesAlphanumericCnpj()
    {
        // Arrange
        $cnpj = '1a2b3c4d000195';

        // Act
        $this->recipient->setHolderDocument($cnpj);

        // Assert
        $this->assertSame('1A2B3C4D000195', $this->recipient->getHolderDocument());
    }

    public function testSetHolderDocumentStripsFormattingFromAlphanumericCnpj()
    {
        // Arrange
        $maskedCnpj = '1A.2B3.C4D/0001-95';

        // Act
        $this->recipient->setHolderDocument($maskedCnpj);

        // Assert
        $this->assertSame('1A2B3C4D000195', $this->recipient->getHolderDocument());
    }

    public function testSetHolderDocumentStripsFormattingFromNumericCnpj()
    {
        // Arrange
        $maskedCnpj = '12.345.678/0001-95';

        // Act
        $this->recipient->setHolderDocument($maskedCnpj);

        // Assert
        $this->assertSame('12345678000195', $this->recipient->getHolderDocument());
    }

    // -------------------------------------------------------------------------
    // setHolderDocument — unhappy paths
    // -------------------------------------------------------------------------

    public function testSetHolderDocumentThrowsOnEmptyString()
    {
        // Arrange
        $this->expectException(\Throwable::class);

        // Act
        $this->recipient->setHolderDocument('');
    }

    public function testSetHolderDocumentThrowsWhenOnlyFormattingChars()
    {
        // Arrange
        $this->expectException(\Throwable::class);

        // Act
        $this->recipient->setHolderDocument('...-/');
    }
}
