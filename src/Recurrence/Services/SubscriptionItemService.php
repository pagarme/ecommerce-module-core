<?php

namespace Pagarme\Core\Recurrence\Services;

use Pagarme\Core\Kernel\Interfaces\PlatformProductInterface;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class SubscriptionItemService
{
    public function updateStock($items)
    {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $product = $this->getProductDecorated($item->getCode());
            $product->decreaseStock($item->getQuantity());
        }
    }

    public function getProductDecorated($code)
    {
        $productDecorator =
            Magento2CoreSetup::get(
                Magento2CoreSetup::CONCRETE_PRODUCT_DECORATOR_CLASS
            );

        /**
         * @var PlatformProductInterface $product
         */
        $product = new $productDecorator();
        $product->loadByEntityId($code);

        return $product;
    }
}