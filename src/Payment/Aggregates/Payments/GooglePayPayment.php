<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Payment\ValueObjects\PaymentMethod;
use PagarmeCoreApiLib\Models\CreateCardRequest;
use PagarmeCoreApiLib\Models\CreateCreditCardPaymentRequest;

final class GooglePayPayment extends AbstractPayment
{

    /**
     * @var array $additionalInformation
     */
    public $additionalInformation;


    /**
     * @return array
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    /**
     * @param array $additionalInformation
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;
    }

    static public function getBaseCode()
    {
        return "credit_card";
    }


    public function getGooglePayData()
    {
        return '{"signature":"MEYCIQCRzAfMhRLFGUs+qj/wJkmE8bY2FDlqV3YYf+XCWMeXuAIhAJubOjThsiQUsp8W0OqV1zQU4KOH0o3SRek6R4mAd+Zl","intermediateSigningKey":{"signedKey":"{\"keyValue\":\"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEoBdGL1g8Rv5S1QygV/Bu4VuRtMBhxV4rzuHbEY2/yhUqjhpL836/s6C9SgpFS5irBqjb2c2clHX4WJ9y6wdc7A\\u003d\\u003d\",\"keyExpiration\":\"1716572087769\"}","signatures":["MEQCIEFaI5O5+8Kuj0K9MEP9d1Psf29dnwdBt1YeWzDlPS8tAiAh2VBGXfpP+JcBZ2N7cziOMNLRQs8h6zm/HDG9UrK/xg\u003d\u003d"]},"protocolVersion":"ECv2","signedMessage":"{\"encryptedMessage\":\"MXsnfpwAaPfgypF6VIG4KT7kf1+yhEK0CeZmncc72OtvUiZyLuDfvnmqK1858mN8QFrnwaE0F5J7DyySC8HzQd9dC9TNl0nn1dZbjPPXxCoFEDFcyRbpWrAnLSt2dq2MKGDk6j/pAvP8kS/JtROoiFBVScncDUlejkqKr7m3sYZQcZKI27VbU7V9QxQ0sw3SvFEk0gKcbiwkUYHac5cDa0OsIfBqx8i5M4hhQgGVCaoHggIzYtvorTKydmk3r0yXf6qjTazjA5aO0Tur1VMM+r+OZNxF8r6nsu+MQeFIfzXFKGIgIeTR+SJ9RCYf/WUlOzlqJIFutRnrDmRj3v7pGVTTIqB/uv7BczFcqxRWqrXFaVva7RapCktK9vUFTuFqMmbR3o/Mo7DEzeOhmIfhWAFFXRtDFAw8V7zUTsyERtTIZBBh5HigxRKDZUNyso2D6DnNeLtwO2Xv31ek/VCaUIQG/e3+oDaPo+BfEVbUVN2Oa1WYa7tWYG9qNZxqSsaTpP8947NK9sODMc8yVVM/PIb/+Q6SfkgH+llVqceiXMg2mg3iUyi3\",\"ephemeralPublicKey\":\"BGaxW/3pKm/7EWt3SnKieXJBqBsbayU6MuULnhNfZFjI7YJpQGvKhl5Vn5WxgBAQ3Q0+rwP3/TLZ4sX5h55NMbU\\u003d\",\"tag\":\"PU/4tqGfueNw8oF8hLmIOz63euhd4Sv0M9sAyztmCp0\\u003d\"}"}';
    }

    /**
     * Encode this object to JSON
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $objPayload = new \stdClass();
        $objPayload->type = "google_pay";
        $objPayload->google_pay = "{}";
        $payload = json_encode((array)$objPayload);
        
        $statementDescriptor = "TESTGOOGLE";
        $cardRequest = new CreateCreditCardPaymentRequest($payload, $statementDescriptor);
        return 'TESTE'; // $cardRequest->jsonSerialize();
    }
    
    /**
     * @return CreateCreditCardPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        
        $objPayload = new \stdClass();
        $objPayload->type = "google_pay";
        $objPayload->google_pay = "{}";
        $payload = json_encode((array)$objPayload);
        
        $statementDescriptor = "TESTGOOGLE";
        $cardRequest = new CreateCreditCardPaymentRequest($payload, $statementDescriptor);
        return "TESTE";// $cardRequest;
    }
}
