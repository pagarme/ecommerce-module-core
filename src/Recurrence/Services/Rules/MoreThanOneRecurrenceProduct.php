<?php

namespace Mundipagg\Core\Recurrence\Services\Rules;

use Mundipagg\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;

class MoreThanOneRecurrenceProduct implements RuleInterface
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
        $canAddRecurrenceProductWithRecurrenceProduct =
            $this->recurrenceConfig
                ->isPurchaseRecurrenceProductWithRecurrenceProduct();

        $messageConflictRecurrence =
            $this->recurrenceConfig
                ->getConflictMessageRecurrenceProductWithRecurrenceProduct();

        if (
            !$canAddRecurrenceProductWithRecurrenceProduct  &&
            (!$currentProduct->isNormalProduct() && !empty($productListInCart->getRecurrenceProducts()))
        ) {
            $this->setError($messageConflictRecurrence);
        }

        return;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($error)
    {
        $this->error = $error;
    }
}