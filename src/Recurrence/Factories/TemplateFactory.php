<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Recurrence\Aggregates\Template;
use Mundipagg\Core\Recurrence\ValueObjects\DueValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\RepetitionValueObject;

class TemplateFactory implements FactoryInterface
{
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

        $dueAt = new DueValueObject();
        $dueAt
            ->setType($postData['expiry_type'])
            ->setValue($postData['expiry_date'])
        ;

        foreach ($postData['intervals'] as $interval) {
            $repetition = new RepetitionValueObject();
            $repetition
                ->setFrequency($interval['frequency'])
                ->setIntervalType($interval['type'])
                ->setCycles($interval['cycles']);

            if (isset($interval['discountValue'])) {
                $repetition
                    ->setDiscountValue($interval['discountValue'])
                    ->setDiscountType($interval['discountType']);
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

        $dueAt = new DueValueObject();
        $dueAt
            ->setType($dbData['due_type'])
            ->setValue($dbData['due_value'])
        ;

        $discountTypes = explode(',', $dbData['discount_type']);
        $discountValues = explode(',', $dbData['discount_value']);
        $intervalTypes = explode(',', $dbData['interval_type']);
        $frequencies = explode(',', $dbData['frequency']);
        $cycles = explode(',', $dbData['cycles']);

        foreach ($discountValues as $index => $discountValue) {
            $repetition = new RepetitionValueObject();
            $repetition
                ->setIntervalType($intervalTypes[$index])
                ->setFrequency($frequencies[$index])
                ->setCycles($cycles[$index]);

            if ($discountValue > 0) {
                $repetition
                    ->setDiscountType($discountTypes[$index])
                    ->setDiscountValue($discountValues[$index]);
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

            $dueAt = new DueValueObject();
            $dueAt
                ->setType($data->dueAt->type)
                ->setValue($data->dueAt->value)
            ;

            foreach ($data->repetitions as $repetition) {
                $_repetition = new RepetitionValueObject();
                $_repetition
                    ->setCycles($repetition->cycles)
                    ->setFrequency($repetition->frequency)
                    ->setIntervalType($repetition->intervalType);
                if ($repetition->discountType) {
                    $_repetition
                        ->setDiscountType($repetition->discountType)
                        ->setDiscountValue($repetition->discountValue);
                }
                $template->addRepetition($_repetition);
            }

            $template->setDueAt($dueAt);

            return $template;
        }
        throw new \Exception('Invalid json data!');
    }
}