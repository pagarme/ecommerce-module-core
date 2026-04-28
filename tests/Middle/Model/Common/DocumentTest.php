<?php

namespace Pagarme\Core\Test\Middle\Model\Common;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Middle\Model\Common\Document;

class DocumentTest extends TestCase
{
    // -------------------------------------------------------------------------
    // getDocumentWithoutMask — strips formatting
    // -------------------------------------------------------------------------

    public function testGetDocumentWithoutMaskStripsCpfMask()
    {
        // Arrange
        $document = new Document('123.456.789-01');

        // Act
        $result = $document->getDocumentWithoutMask();

        // Assert
        $this->assertSame('12345678901', $result);
    }

    public function testGetDocumentWithoutMaskStripsNumericCnpjMask()
    {
        // Arrange
        $document = new Document('12.345.678/0001-95');

        // Act
        $result = $document->getDocumentWithoutMask();

        // Assert
        $this->assertSame('12345678000195', $result);
    }

    public function testGetDocumentWithoutMaskStripsAlphanumericCnpjMask()
    {
        // Arrange
        $document = new Document('1A.2B3.C4D/0001-95');

        // Act
        $result = $document->getDocumentWithoutMask();

        // Assert
        $this->assertSame('1A2B3C4D000195', $result);
    }

    // -------------------------------------------------------------------------
    // getDocumentWithoutMask — uppercase
    // -------------------------------------------------------------------------

    public function testGetDocumentWithoutMaskUppercasesLowercaseLetters()
    {
        // Arrange
        $document = new Document('1a2b3c4d000195');

        // Act
        $result = $document->getDocumentWithoutMask();

        // Assert
        $this->assertSame('1A2B3C4D000195', $result);
    }

    public function testGetDocumentWithoutMaskUppercasesLowercaseLettersWithMask()
    {
        // Arrange
        $document = new Document('1a.2b3.c4d/0001-95');

        // Act
        $result = $document->getDocumentWithoutMask();

        // Assert
        $this->assertSame('1A2B3C4D000195', $result);
    }

    // -------------------------------------------------------------------------
    // getDocumentWithoutMask — already clean input
    // -------------------------------------------------------------------------

    public function testGetDocumentWithoutMaskKeepsAlreadyCleanCpf()
    {
        // Arrange
        $document = new Document('12345678901');

        // Act
        $result = $document->getDocumentWithoutMask();

        // Assert
        $this->assertSame('12345678901', $result);
    }

    public function testGetDocumentWithoutMaskKeepsAlreadyCleanAlphanumericCnpj()
    {
        // Arrange
        $document = new Document('1A2B3C4D000195');

        // Act
        $result = $document->getDocumentWithoutMask();

        // Assert
        $this->assertSame('1A2B3C4D000195', $result);
    }
}
