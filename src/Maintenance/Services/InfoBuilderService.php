<?php

namespace Mundipagg\Core\Maintenance\Services;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Mundipagg\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;
use Mundipagg\Core\Payment\Aggregates\Address;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Item;
use Mundipagg\Core\Payment\Aggregates\Order;
use Mundipagg\Core\Payment\Aggregates\Payments\NewCreditCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\SavedCreditCardPayment;
use Mundipagg\Core\Payment\ValueObjects\CardToken;
use Mundipagg\Core\Payment\ValueObjects\CustomerPhones;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use Mundipagg\Core\Payment\ValueObjects\Phone;

class InfoBuilderService
{

    private function paymentTest()
    {
        $user = new Customer();
        $user->setType(CustomerType::individual());

        $test = new Order();

        for ($i = 1; $i <= 3; $i++) {
            $card = new NewCreditCardPayment();
            $card->setAmount($i * 10000);
            $card->setCardToken(new CardToken('token_xxxxxxxxxxxxxxx' . $i));
            $card->setStatementDescriptor('STATEMENT');
            $card->setInstallments($i * 2);
            $test->addPayment($card);

            $item = new Item;
            $item->setAmount(10000);
            $item->setQuantity($i);
            $item->setDescription('Item ' . $i);

            $test->addItem($item);

        }

        $test->setCode('00001');
        $test->setAntifraudEnabled(false);
        $test->setCustomer($this->createCustomer());

        return [$test];

    }

    private function createCustomer()
    {
        $customer = new Customer;

        $customer->setName('Custom Er');
        $customer->setEmail('test@test.com');
        $customer->setDocument('00000000000');
        $customer->setType(CustomerType::individual());
        $customer->setPhones(
            CustomerPhones::create([
                new Phone('55','21','12345678'),
                new Phone('55','21','999999999')
            ])
        );

        $address = new Address();

        $address->setStreet('Rua');
        $address->setNumber('99');
        $address->setNeighborhood('Bairro');
        $address->setComplement('Complemento');
        $address->setCity('Cidade');
        $address->setCountry('Pais');
        $address->setZipCode('20000-000');

        $customer->setAddress($address);

        return $customer;
    }


    /**
     *
     * @param  array $query
     * @return string|array
     */
    public function buildInfoFromQueryArray(array $query)
    {

        return $this->paymentTest();

        $infos = [];
        if (!$this->isTokenValid($query)) {
            return [];
        }

        foreach ($query as $parameter => $value) {
            $infoRetriever = $this->getInfoRetrieverServiceFor($parameter);
            if ($infoRetriever === null) {
                continue;
            }

            $data = $infoRetriever->retrieveInfo($value);
            if (is_string($data)) {
                return $data;
            }
            $infos[$parameter] = $data;
        }
        return $infos;
    }

    /**
     *
     * @param  $parameter
     * @return null|InfoRetrieverServiceInterface
     */
    private function getInfoRetrieverServiceFor($parameter)
    {
        $infoRetrieverServiceClass =
            'Mundipagg\\Core\\Maintenance\\Services\\' .
            ucfirst($parameter) .
            'InfoRetrieverService';

        if (!class_exists($infoRetrieverServiceClass)) {
            return null;
        }

        return new $infoRetrieverServiceClass();
    }


    private function isTokenValid($token)
    {
        if (is_array($token)) {
            if (!isset($token['token'])) {
                return false;
            }
            $token = $token['token'];
        }

        $passedKeyHash = base64_decode($token);

        $moduleConfig = AbstractModuleCoreSetup::getModuleConfiguration();
        $secretKey = $moduleConfig->getSecretKey();

        if ($secretKey === null) {
            return false;
        }

        $secretKeyHash = $this->generateKeyHash($secretKey->getValue());

        return $secretKeyHash === $passedKeyHash;
    }

    public function generateKeyHash($keyValue)
    {
        return hash('sha512', $keyValue);
    }
}