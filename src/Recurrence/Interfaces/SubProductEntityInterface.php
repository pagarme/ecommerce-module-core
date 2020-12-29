<?php

namespace Mundipagg\Core\Recurrence\Interfaces;

use Mundipagg\Core\Recurrence\Aggregates\SubProduct;

interface SubProductEntityInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return \stdClass
     */
    public function convertToSdkRequest();

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return SubProduct
     */
    public function setProductId($productId);

    /**
     * @return int
     */
    public function getCycles();

    /**
     * @param int $cycles
     * @return SubProduct
     */
    public function setCycles($cycles);

}