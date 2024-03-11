<?php

namespace Pagarme\Core\Middle\Factory;

use Exception;
use InvalidArgumentException;
use Pagarme\Core\Middle\Model\Marketplace\BankAccount;
use Pagarme\Core\Middle\Model\Marketplace\IndividualRegisterInformation;
use Pagarme\Core\Middle\Model\Recipient;
use Pagarme\Core\Middle\Model\Marketplace\CorporationRegisterInformation;
use Pagarme\Core\Middle\Model\Marketplace\ManagingPartner;
use Pagarme\Core\Middle\Model\Phones;
use Pagarme\Core\Middle\Model\Address;
use Pagarme\Core\Middle\Model\Marketplace\TransferSettings;

class RecipientFactory
{
    /**
     * Undocumented function
     *
     * @param [type] $recipientData
     * @return \Pagarme\Core\Middle\Model\Recipient
     */
    public function createRecipient($recipientData)
    {
        $recipientType = ['register_information']['type'];
        if ($recipientType !== Recipient::INDIVIDUAL && $recipientType !== Recipient::CORPORATION) {
            return new InvalidArgumentException("This request is not valid");
        }

        $bankAccount = $this->createBankAccount($recipientData);
        $transferSettings = $this->createTransferSettings($recipientData);
        if ($recipientData['register_information']['type'] === Recipient::INDIVIDUAL) {
            $registerInformation = $this->createIndividual($recipientData['register_information'], $bankAccount);
        }
        if ($recipientData['register_information']['type'] === Recipient::CORPORATION) {
            $registerInformation = $this->createCorportarion($recipientData['register_information'], $bankAccount);
        }
        return $this->createBaseRecipient($bankAccount, $transferSettings, $registerInformation, $recipientData['code']);
    }

    private function createCorportarion($recipientData)
    {
        $registerInformation = new CorporationRegisterInformation();
        $registerInformation->setEmail($recipientData['email']);
        $registerInformation->setDocumentNumber($recipientData['document']);
        $registerInformation->setType($recipientData['type']);
        foreach ($recipientData['phone_number'] as $phone) {
            $phoneNumber = new Phones($phone['type'], $phone['number']);
            $registerInformation->addPhoneNumbers($phoneNumber->convertToRegisterInformationPhonesRequest());
        }
        $registerInformation->setCompanyName($recipientData['company_name']);
        $registerInformation->setTradingName($recipientData['trading_name']);
        $registerInformation->setAnnualRevenue($recipientData['']);
        $registerInformation->setCnae($recipientData['cnae']);
        $registerInformation->setFoundingDate($recipientData['founding_date']);
        foreach ($recipientData['managing_partners'] as $partner) {
            $registerInformation->addManagingPartners($this->createManagingPartner($partner));
        }
        $registerInformation->setAddress($this->createAddress($recipientData['main_address']));
        return $registerInformation->convertToSDKRequest();
    }


    private function createIndividual($recipientData)
    {
        $registerInformation = new IndividualRegisterInformation();
        $registerInformation->setType($recipientData['type']);
        $registerInformation->setDocumentNumber($recipientData['document']);
        $registerInformation->setEmail($recipientData['email']);
        $registerInformation->setName($recipientData['name']);
        $registerInformation->setSiteUrl($recipientData['site_url']);
        $registerInformation->setMotherName($recipientData['mother_name']);
        foreach ($recipientData['phone_number'] as $phone) {
            $phoneNumber = new Phones($phone['type'], $phone['number']);
            $registerInformation->addPhoneNumbers($phoneNumber->convertToRegisterInformationPhonesRequest());
        }
        $registerInformation->setBirthdate($recipientData['birthdate']);
        $registerInformation->setMonthlyIncome($recipientData['monthly_income']);
        $registerInformation->setProfessionalOccupation($recipientData['professional_occupation']);
        $registerInformation->setAddress($this->createAddress($recipientData['address']));
        return $registerInformation->convertToSDKRequest();
    }

    private function createManagingPartner($partner)
    {
        $newPartner = new ManagingPartner();
        $newPartner->setType($partner['type']);
        $newPartner->setName($partner['name']);
        $newPartner->setDocumentNumber($partner['document']);
        $newPartner->setEmail($partner['email']);
        $newPartner->setMotherName($partner['mother_name']);
        foreach ($partner['phone_number'] as $phone) {
            $phoneNumber = new Phones($phone['type'], $phone['number']);
            $newPartner->addPhoneNumbers($phoneNumber->convertToRegisterInformationPhonesRequest());
        }
        $newPartner->setBirthdate($partner['birthdate']);
        $newPartner->setMonthlyIncome($partner['monthly_income']);
        $newPartner->setProfessionalOccupation($partner['professional_occupation']);
        $newPartner->setAddress($this->createAddress($partner['address']));
        $newPartner->setSelfDeclaredLegalRepresentative(true);
        return $newPartner->convertToArray();
    }
    private function createBankAccount($recipientData)
    {
        $bankAccount = new BankAccount();
        $bankAccount->setHolderName($recipientData['holder_name']);
        $bankAccount->setHolderType($recipientData["holder_document_type"]);
        $bankAccount->setHolderDocument($recipientData["holder_document"]);
        $bankAccount->setBank($recipientData["bank"]);
        $bankAccount->setBranchNumber($recipientData["branch_number"]);
        $bankAccount->setBranchCheckDigit($recipientData["account_check_digit"]);
        $bankAccount->setAccountNumber($recipientData["account_number"]);
        $bankAccount->setType($recipientData["account_type"]);
        $bankAccount->setMetadata(null);
        return $bankAccount;
    }
    private function createTransferSettings($recipientData)
    {
        $transferSettings = new TransferSettings(
            $recipientData['transfer_enabled'],
            $recipientData['transfer_interval'],
            $recipientData['transfer_day']
        );
        $transferSettings->convertToArray();
    }

    private function createBaseRecipient($bankAccount, $transferSettings, $registerInformation, $code)
    {
        $recipient = new Recipient();
        $recipient->setBankAccount($bankAccount);
        $recipient->setTransferSettings($transferSettings);

        // Product team decision
        $recipient->setAutomaticAnticipationSettings(null);

        $recipient->setRegisterInformation($registerInformation);
        $recipient->setCode($code);
        $recipient->convertToCreateRequest();
    }

    private function createAddress($addressFields)
    {
        $address = new Address();
        $address->setZipCode($addressFields['zip_code']);
        $address->setStreet($addressFields['street']);
        $address->setStreetNumber($addressFields['street_number']);
        $address->setComplementary($addressFields['complementary']);
        $address->setReferencePoint($addressFields['reference_point']);
        $address->setNeighborhood($addressFields['neighbordhood']);
        $address->setState($addressFields['state']);
        $address->setCity($addressFields['city']);
        return $address->convertToCreateRegisterInformationAddressRequest();
    }
}
