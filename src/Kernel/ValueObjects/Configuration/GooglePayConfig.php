<?php

namespace Pagarme\Core\Kernel\ValueObjects\Configuration;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

class GooglePayConfig extends AbstractValueObject
{
    /** @var bool */
    private $enabled;

    /** @var string */
    private $title;
    private $merchantId;
    private $merchantName;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return PixConfig
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return PixConfig
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    /**
     * @param string $merchant_id
     * @return PixConfig
     */
    public function setMerchantId($merchant_id)
    {
        $this->merchantId = $merchant_id;
        return $this;
    }
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
        return $this;
    }
    
    public function getMerchantName()
    {
        return $this->merchantName;
    }
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    
    public function isEqual($object)
    {
        return false;
    }
    /**
      * Specify data which should be serialized to JSON
      * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
      * @return mixed data which can be serialized by <b>json_encode</b>,
      * which is a value of any type other than a resource.
      * @since 5.4.0
    */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            "enabled" => $this->isEnabled(),
            "title" => $this->getTitle(),
            "merchantId" => $this->getMerchantId(),
            "merchantName" => $this->getMerchantName(),
        ];
    }
}
