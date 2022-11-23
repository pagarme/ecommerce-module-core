<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Core\Kernel\Log;

/**
 * Class BlurData
 */
class BlurData
{
    /**
     * @param string $method
     * @return string
     */
    public function getBlurMethod(string $method)
    {
        return 'blur' . str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));
    }

    /**
     * @param string $value
     * @param $delimiter
     * @return string
     */
    private function blurStringSensitiveData(string $value, $delimiter)
    {
        $displayed = substr($value, 0, $delimiter);
        $blur = str_repeat("*", strlen($value));
        $blur = substr($blur, $delimiter);
        $result = "$displayed $blur";
        return $result;
    }

    /**
     * @param $string
     * @return string
     */
    private function blurEmailSensitiveData($string)
    {
        $displayed = substr($string, 0, 3);
        $final = substr($string, strpos($string, "@"));
        $result = "$displayed***$final";
        return $result;
    }

    /**
     * @param string $name
     * @return string
     */
    public function blurName(string $name)
    {
        return $this->blurStringSensitiveData($name, 5);
    }

    /**
     * @param array $billingAddress
     * @return array
     */
    public function blurBillingAddress(array $billingAddress)
    {
        return $this->blurAddress($billingAddress);
    }

    /**
     * @param string $email
     * @return string
     */
    public function blurEmail(string $email)
    {
        return $this->blurEmailSensitiveData($email);
    }

    /**
     * @param array $street
     * @return array
     */
    public function blurStreet(string $street)
    {
        return $this->blurStringSensitiveData($street, 8);
    }

    /**
     * @param string $document
     * @return string
     */
    public function blurDocument(string $document)
    {
        return preg_replace('/\B[^@.]/', '*', $document);
    }

    /**
     * @param array $line1
     * @return array
     */
    public function blurLine1(string $line1)
    {
        return $this->blurStringSensitiveData($line1, 8);
    }

    /**
     * @param string $holderName
     * @return string
     */
    public function blurHolderName(string $holderName)
    {
        return preg_replace('/^.{8}/', '$1**', $holderName);
    }

    /**
     * @param array $zipCode
     * @return array
     */
    public function blurZipCode(string $zipCode)
    {
        return $this->blurStringSensitiveData($zipCode, 5);
    }

    /**
     * @param array $recipientName
     * @return array
     */
    public function blurRecipientName(string $recipientName)
    {
        return $this->blurStringSensitiveData($recipientName, 5);
    }

    /**
     * @param array $customer
     * @return array
     */
    public function blurCustomer(array $customer)
    {
        foreach ($customer as $key => $value) {
            $blurMethod = $this->getBlurMethod($key);
            if (method_exists($this, $blurMethod)) {
                $customer[$key] = $this->{$blurMethod}($value);
                continue;
            }
            $customer[$key] = '***********';
        }
        return $customer;
    }

    /**
     * @param array $address
     * @return array
     */
    public function blurAddress(array $address)
    {
        foreach ($address as $key => $value) {
            $blurMethod = $this->getBlurMethod($key);
            if (method_exists($this, $blurMethod)) {
                $address[$key] = $this->{$blurMethod}($value);
                continue;
            }
            $address[$key] = '***********';
        }
        return $address;
    }

    /**
     * @param array $shipping
     * @return array
     */
    public function blurShipping(array $shipping)
    {
        foreach ($shipping as $key => $value) {
            $blurMethod = $this->getBlurMethod($key);
            if (method_exists($this, $blurMethod)) {
                $shipping[$key] = $this->{$blurMethod}($value);
                continue;
            }
            $shipping[$key] = '***********';
        }
        return $shipping;
    }

    /**
     * @param array $payments
     * @return array
     */
    public function blurPayments(array $payments)
    {
        foreach ($payments as &$payment) {
            foreach ($payment as $method => $value) {
                $blurMethod = $this->getBlurMethod($method);
                if (method_exists($this, $blurMethod)) {
                    $payment[$method] = $this->{$blurMethod}($value);
                }
            }
        }
        return $payments;
    }

    /**
     * @param array $creditCard
     * @return array
     */
    public function blurCreditCard(array $creditCard)
    {
        foreach ($creditCard as $method => $value) {
            $blurMethod = $this->getBlurMethod($method);
            if (method_exists($this, $blurMethod)) {
                $creditCard[$method] = $this->{$blurMethod}($value);
            }
        }
        return $creditCard;
    }

    /**
     * @param array $payments
     * @return array
     */
    public function blurLastTransaction(array $lastTransaction)
    {
        foreach ($lastTransaction as $method => &$value) {
            $blurMethod = $this->getBlurMethod($method);
            if (method_exists($this, $blurMethod)) {
                $lastTransaction[$method] = $this->{$blurMethod}($value);
            }
        }
        return $lastTransaction;
    }

    /**
     * @param array $card
     * @return array
     */
    public function blurCard(array $card)
    {
        foreach ($card as $method => $value) {
            $blurMethod = $this->getBlurMethod($method);
            if (method_exists($this, $blurMethod)) {
                $card[$method] = $this->{$blurMethod}($value);
            }
        }
        return $card;
    }

    /**
     * @param array $charges
     * @return array
     */
    public function blurCharges(array $charges)
    {
        foreach ($charges as &$charge) {
            foreach ($charge as $method => &$value) {
                $blurMethod = $this->getBlurMethod($method);
                if (method_exists($this, $blurMethod)) {
                    $charge[$method] = $this->{$blurMethod}($value);
                }
            }
        }
        return $charges;
    }
}
