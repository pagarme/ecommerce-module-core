<?php

namespace Mundipagg\Core\Recurrence\Services\CartRules;

use Mundipagg\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;

class NormalWithRecurrenceProduct implements RuleInterface
{
    /**
     * @var RecurrenceConfig
     */
    protected $recurrenceConfig;
    private $error;

    public function __construct(RecurrenceConfig $recurrenceConfig)
    {
        $this->recurrenceConfig = $recurrenceConfig;
    }

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        $canAddNormalProductWithRecurrenceProduct =
            $this->recurrenceConfig
                ->isPurchaseRecurrenceProductWithNormalProduct();

        $messageConflictRecurrence =
            $this->recurrenceConfig
                ->getConflictMessageRecurrenceProductWithNormalProduct();

        if (
            !$canAddNormalProductWithRecurrenceProduct  &&
            ($currentProduct->isNormalProduct() && !empty($productListInCart->getRecurrenceProducts()))
        ) {
            $this->setError($messageConflictRecurrence);
            return;
        }

        if (
            !$canAddNormalProductWithRecurrenceProduct  &&
            (!$currentProduct->isNormalProduct() && !empty($productListInCart->getNormalProducts()))
        ) {
            $this->setError($messageConflictRecurrence);
            return;
        }

        return;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function setError($error)
    {
        $this->error = $error;
    }
}