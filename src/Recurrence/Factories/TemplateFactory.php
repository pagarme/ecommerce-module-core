<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Aggregates\Template;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\DueValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class TemplateFactory implements FactoryInterface
{
    private function snake2Camel($snake)
    {
        return  lcfirst(
            str_replace('_', '', ucwords($snake, '_'))
        );
    }

    /**
     *
     * @param  array $postData
     * @return AbstractEntity
     * @throws \Exception
     */
    public function createFromPostData($postData)
    {
        $template = new Template();

        $template
            ->setName($postData['name'])
            ->setDescription($postData['description'])
        ;

        if (isset($postData['single'])) {
            $template->setIsSingle($postData['single']);
        }

        if (isset($postData['trial'])) {
            $template->setTrial(intval($postData['trial']));
        }

        $paymentMethods =
            isset($postData['payment_method']) ? $postData['payment_method'] : [];
        foreach( $paymentMethods as $paymentMethod)
        {
            switch($paymentMethod)
            {
                case 'credit_card':
                    $template
                        ->setAcceptCreditCard(true)
                        ->setAllowInstallments($postData['allow_installment'])
                        ->addInstallments(explode(",", $postData['installments']));
                    break;
                case 'boleto':
                    $template->setAcceptBoleto(true);
                    break;
            }
        }

        $typeMethod = $postData['expiry_type'];
        $typeMethod = $this->snake2Camel($typeMethod);
        $dueAt = DueValueObject::$typeMethod($postData['expiry_date']);

        foreach ($postData['intervals'] as $interval) {

            $intervalMethod = $interval['type'];
            $intervalValueObject = IntervalValueObject::$intervalMethod($interval['frequency']);

            $repetition = new Repetition();
            $repetition
                ->setInterval($intervalValueObject)
                ->setCycles($interval['cycles']);

            if (isset($interval['discountValue'])) {
                $discountMethod = $interval['discountType'];
                $discount = DiscountValueObject::$discountMethod($interval['discountValue']);
                $repetition->setDiscount($discount);
            }
            $template->addRepetition($repetition);
        }

        $template->setDueAt($dueAt);

        return $template;
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     * @throws \Exception
     */
    public function createFromDbData($dbData)
    {
        $template = new Template();

        $template
            ->setId($dbData['id'])
            ->setName($dbData['name'])
            ->setDescription($dbData['description'])
            ->setIsSingle($dbData['is_single'])
            ->setAcceptBoleto($dbData['accept_boleto'])
            ->setAcceptCreditCard($dbData['accept_credit_card'])
            ->setAllowInstallments($dbData['allow_installments'])
            ->setTrial($dbData['trial'])
            ->addInstallments(json_decode($dbData['installments'], true))
        ;

        $typeMethod = $dbData['due_type'];
        $typeMethod = $this->snake2Camel($typeMethod);
        $dueAt = DueValueObject::$typeMethod($dbData['due_value']);

        $discountTypes = explode(',', $dbData['discount_type']);
        $discountValues = explode(',', $dbData['discount_value']);
        $intervalTypes = explode(',', $dbData['interval_type']);
        $frequencies = explode(',', $dbData['frequency']);
        $cycles = explode(',', $dbData['cycles']);

        foreach ($discountValues as $index => $discountValue) {

            $intervalMethod = $intervalTypes[$index];
            $interval = IntervalValueObject::$intervalMethod($frequencies[$index]);

            $repetition = new Repetition();
            $repetition
                ->setInterval($interval)
                ->setCycles($cycles[$index]);

            if ($discountValue > 0) {
                $discountMethod = $discountTypes[$index];
                $discount = DiscountValueObject::$discountMethod($discountValues[$index]);
                $repetition->setDiscount($discount);
            }

            $template->addRepetition($repetition);
        }

        $template->setDueAt($dueAt);

        return $template;
    }

    public function createFromJson($jsonData)
    {
        $data = json_decode(utf8_decode($jsonData));
        if (json_last_error() == JSON_ERROR_NONE) {
            $template = new Template();

            $installments = [];
            if (isset($data->installments)) {
                $installments = json_decode($data->installments);
            }
            if (!is_array($installments)) {
                $installments = explode(",", $data->installments);
            }

            $trial = 0;
            if (!empty($data->trial)) {
                $trial = $data->trial;
            }

            $template
                ->setName($data->name)
                ->setDescription($data->description)
                ->setIsSingle($data->isSingle)
                ->setAcceptBoleto($data->acceptBoleto)
                ->setAcceptCreditCard($data->acceptCreditCard)
                ->setAllowInstallments($data->allowInstallments)
                ->setTrial($trial)
                ->addInstallments($installments)
            ;

            if (isset($data->id)) {
                $template->setId($data->id);
            }

            $typeMethod = $data->dueAt->type;
            $typeMethod = $this->snake2Camel($typeMethod);
            $dueAt = DueValueObject::$typeMethod($data->dueAt->value);

            foreach ($data->repetitions as $repetition) {

                $intervalMethod = $repetition->intervalType;
                $interval = IntervalValueObject::$intervalMethod($repetition->frequency);

                $_repetition = new Repetition();
                $_repetition
                    ->setCycles($repetition->cycles)
                    ->setInterval($interval);

                if ($repetition->discountType) {
                    $discountMethod = $repetition->discountType;
                    $discount = DiscountValueObject::$discountMethod($repetition->discountValue);
                    $_repetition
                        ->setDiscount($discount);
                }
                $template->addRepetition($_repetition);
            }

            $template->setDueAt($dueAt);

            return $template;
        }
        throw new \Exception('Invalid json data!');
    }
}