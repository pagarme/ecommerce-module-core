<?php


namespace Mundipagg\Core\Recurrence\Factories;


use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class SubProductFactory implements FactoryInterface
{
    /**
     *
     * @param array $postData
     * @return AbstractEntity
     * @throws \Exception
     */
    public function createFromPostData($postData)
    {
        $subProduct = new SubProduct();

        if (!empty($postData['productRecurrenceId'])) {
            $subProduct->setProductRecurrenceId($postData['productRecurrenceId']);
        }

        if (!empty($postData['id'])) {
            $subProduct->setId($postData['id']);
        }

        if (!empty($postData['product_id'])) {
            $subProduct->setProductId($postData['product_id']);
        }

        if (!empty($postData['name'])) {
            $subProduct->setName($postData['name']);
        }

        if (!empty($postData['description'])) {
            $subProduct->setDescription($postData['description']);
        }

        if (!empty($postData['price'])) {
            $subProduct->setPrice($postData['price']);
        }

        if (!empty($postData['quantity'])) {
            $subProduct->setQuantity($postData['quantity']);
        }

        if (!empty($postData['cycles'])) {
            $subProduct->setCycles($postData['cycles']);
        }

        return $subProduct;
    }

    /**
     *
     * @param array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }
}