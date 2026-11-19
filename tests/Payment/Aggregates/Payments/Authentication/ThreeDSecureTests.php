<?php

namespace Pagarme\Core\Test\Payment\Aggregates\Payments\Authentication;

use Pagarme\Core\Payment\Aggregates\Payments\Authentication\Authentication;
use Pagarme\Core\Payment\Aggregates\Payments\Authentication\Type\ThreeDSecure;
use PHPUnit\Framework\TestCase;
use stdClass;

class ThreeDSecureTests extends TestCase
{
    const RISK_ID = '2ab4fb96-1cb7-4a1e-9f4c-6a2b9a3b1c10';

    private function threeDSecureObject($riskId = null)
    {
        $object = new stdClass();
        $object->mpi = 'pagarme';
        $object->transactionId = '6e5b0ca7-3f38-4432-85b8-23a391fc6e91';
        if ($riskId !== null) {
            $object->riskId = $riskId;
        }

        return $object;
    }

    private function authenticationObject(stdClass $threeDSecure)
    {
        $object = new stdClass();
        $object->type = ThreeDSecure::TYPE;
        $object->status = 'Y';
        $object->threeDSecure = $threeDSecure;

        return $object;
    }

    public function testCreatesFromStdClassWithRiskId()
    {
        $threeDSecure = ThreeDSecure::createFromStdClass($this->threeDSecureObject(self::RISK_ID));

        $this->assertSame(self::RISK_ID, $threeDSecure->getRiskId());
    }

    public function testRiskIdIsOptional()
    {
        $threeDSecure = ThreeDSecure::createFromStdClass($this->threeDSecureObject());

        $this->assertNull($threeDSecure->getRiskId());
    }

    public function testConvertsRiskIdToTheSdkRequest()
    {
        $threeDSecure = ThreeDSecure::createFromStdClass($this->threeDSecureObject(self::RISK_ID));

        $request = $threeDSecure->convertToSDKRequest();

        $this->assertSame(self::RISK_ID, $request->riskId);
        $this->assertSame(self::RISK_ID, $request->jsonSerialize()['risk_id']);
    }

    public function testRiskIdTravelsFromTheAuthenticationToTheApiPayload()
    {
        $authentication = Authentication::createFromStdClass(
            $this->authenticationObject($this->threeDSecureObject(self::RISK_ID))
        );

        $payload = $authentication->convertToSDKRequest()->threedSecure->jsonSerialize();

        $this->assertSame(self::RISK_ID, $payload['risk_id']);
        $this->assertSame('pagarme', $payload['mpi']);
        $this->assertSame('6e5b0ca7-3f38-4432-85b8-23a391fc6e91', $payload['transaction_id']);
    }

    public function testPayloadWithoutRiskIdIsUnchanged()
    {
        $threeDSecure = ThreeDSecure::createFromStdClass($this->threeDSecureObject());

        $payload = $threeDSecure->convertToSDKRequest()->jsonSerialize();

        $this->assertArrayNotHasKey('risk_id', $payload);
    }
}
