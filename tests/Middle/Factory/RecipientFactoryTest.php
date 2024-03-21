<?php

namespace Pagarme\Core\Test\Middle\Factory;

use Mockery;
use PHPUnit\Framework\TestCase;
use Pagarme\Core\Middle\Model\Recipient;
use Pagarme\Core\Middle\Factory\RecipientFactory;
use PagarmeCoreApiLib\Models\CreateBankAccountRequest;
use PagarmeCoreApiLib\Models\CreateTransferSettingsRequest;
use PagarmeCoreApiLib\Models\CreateRegisterInformationIndividualRequest;
use PagarmeCoreApiLib\Models\CreateRegisterInformationCorporationRequest;


class RecipientFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        Mockery::close();
    }

    public function testCreateNewIndividualRecipientWithAllDataValid()
    {
        $arrayDataValid = $this->getIndividualArrayData();
        $recipientFactory = new RecipientFactory();
        $result = $recipientFactory->createRecipient($arrayDataValid);
        $this->assertInstanceOf(CreateBankAccountRequest::class, $result->getBankAccount());
        $this->assertInstanceOf(CreateTransferSettingsRequest::class, $result->getTransferSettings());
        $this->assertNull($result->getAutomaticAnticipationSettings());
        $this->assertInstanceOf(CreateRegisterInformationIndividualRequest::class, $result->getRegisterInformation());
        $this->assertInstanceOf(Recipient::class, $result);
    }

    public function testCreateNewCorporationRecipientWithAllDataValid()
    {
        $arrayDataValid = $this->getCorporationArrayData();
        $recipientFactory = new RecipientFactory();
        $result = $recipientFactory->createRecipient($arrayDataValid);
        $this->assertInstanceOf(CreateBankAccountRequest::class, $result->getBankAccount());
        $this->assertInstanceOf(CreateTransferSettingsRequest::class, $result->getTransferSettings());
        $this->assertNull($result->getAutomaticAnticipationSettings());
        $this->assertInstanceOf(CreateRegisterInformationCorporationRequest::class, $result->getRegisterInformation());
        $this->assertInstanceOf(Recipient::class, $result);
    }

    
    /**
     * @expectedException
     */
    public function testeCreateNewRecipientWithEmptyArray()
    {
        $this->expectError();
        $arrayDataValid = [];
        $recipientFactory = new RecipientFactory();
        $recipientFactory->createRecipient($arrayDataValid);
    }

    private function getIndividualArrayData()
    {
        return [
            "register_information" => [
                "webkul_seller" => "3",
                "external_id" => "128693",
                "type" => "individual",
                "document" => "844.553.530-70",
                "name" => "Teste teste",
                "email" => "fabiano@teste.com",
                "site_url" => "https://teste.com",
                "mother_name" => "Teste",
                "birthdate" => "01/03/1989",
                "monthly_income" => "150.000,00",
                "professional_occupation" => "Teste",
                "phone_number" => [
                    [
                        "type" => "home_phone",
                        "number" => "(99) 9999-9999"
                    ],
                    [
                        "type" => "mobile_phone",
                        "number" => "(99) 9111-1111"
                    ]
                ],
                "address" => [
                    "zip_code" => "83408-796",
                    "street" => "Rua Basílio Antunes dos Santos",
                    "street_number" => "123",
                    "complementary" => "Teste Complemento",
                    "reference_point" => "Teste Referencia",
                    "neighborhood" => "Santa Terezinha",
                    "state" => "PR",
                    "city" => "Colombo",
                ]

            ],
            "existing_recipient" => "0",
            "holder_name" => "Teste teste",
            "holder_document_type" => "individual",
            "holder_document" => "844.553.530-70",
            "bank" => "001",
            "branch_number" => "0957",
            "branch_check_digit" => "7",
            "account_number" => "1279208",
            "account_check_digit" => "5",
            "account_type" => "checking",
            "transfer_enabled" => "1",
            "transfer_interval" => "Weekly",
            "transfer_day" => "3"
        ]; 
    }
    private function getCorporationArrayData()
    {
        return [
            "register_information" => [
                "webkul_seller" => "4",
                "external_id" => "150894",
                "type" => "corporation",
                "document" => "18.854.214/0001-90",
                "name" => "Pagarme teste",
                "company_name" => "Naturezza Comercio E Servicos Ltda",
                "trading_name" => "Naturezza Comercio E Servicos",
                "email" => "admin@admin.com",
                "site_url" => "https://teste.com",
                "birthdate" => "01/03/1983",
                "annual_revenue" => "1.500.000,00",
                "corporation_type" => "Sociedade Empresária Limitada",
                "founding_date" => "11/09/2013",
                "phone_number" => [
                    [
                        "type" => "home_phone",
                        "number" => "(99) 9999-9999"
                    ],
                    [
                        "type" => "mobile_phone",
                    ]
                ],
                "main_address" => [
                    "zip_code" => "83408-796",
                    "street" => "Rua Basílio Antunes dos Santos",
                    "street_number" => "123",
                    "neighborhood" => "Santa Terezinha",
                    "complementary" => "",
                    "reference_point" => "",
                    "state" => "PR",
                    "city" => "Colombo"
                ],
                "managing_partners" => [
                    [
                        "name" => "Francisco Candido Dos Reis Neto",
                        "type" => "individual",
                        "document" => "287.943.680-07",
                        "mother_name" => "Teste",
                        "email" => "admin@admin.com",
                        "birthdate" => "01/03/1995",
                        "monthly_income" => "1.500,00",
                        "professional_occupation" => "Sócio-Administrador",
                        "self_declared_legal_representative" => "1",
                        "phone_number" => [
                            [
                                "type" => "home_phone",
                                "number" => "(99) 9999-9999"
                            ],
                            [
                                "type" => "mobile_phone",
                            ]
                        ],
                        "address" => [
                            "zip_code" => "83408-796",
                            "street" => "Rua Basílio Antunes dos Santos",
                            "street_number" => "123",
                            "neighborhood" => "Santa Terezinha",
                            "complementary" => "",
                            "reference_point" => "",
                            "state" => "PR",
                            "city" => "Colombo"
                        ]

                    ]
                ]
            ],
            "holder_name" => "Naturezza Comercio E Servicos Ltda",
            "holder_document_type" => "company",
            "holder_document" => "18.854.214/0001-90",
            "bank" => "001",
            "branch_number" => "0957",
            "branch_check_digit" => "7",
            "account_number" => "1279208",
            "account_check_digit" => "5",
            "account_type" => "checking",
            "transfer_enabled" => "1",
            "transfer_interval" => "Monthly",
            "transfer_day" => "19",

        ]; 
    }
}
