<?php

namespace Pagarme\Core\Middle\Model\Marketplace;

use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PagarmeCoreApiLib\Models\CreateRegisterInformationIndividualRequest;

class IndividualRegisterInformation extends BasePersonInformation  implements ConvertibleToSDKRequestsInterface 
{
    public function convertToSDKRequest()
    {
        return new CreateRegisterInformationIndividualRequest(
            $this->getEmail(),
            $this->getDocumentNumber(),
            $this->getType(),
            $this->getPhoneNumbers(),
            $this->getName(),
            $this->getBirthdate(),
            $this->getMonthlyIncome(),
            $this->getProfessionalOccupation(),
            $this->getAddress()
        );
    }
}
