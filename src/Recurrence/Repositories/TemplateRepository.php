<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;

class TemplateRepository extends AbstractRepository
{

    protected function create(AbstractEntity &$object)
    {
        $templateTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TEMPLATE);

        $query = "
            INSERT INTO `" . $templateTable . "` (
                `is_enabled`,
                `is_single`,
                `name`,
                `description`,
                `accept_credit_card`,
                `accept_boleto`,
                `allow_installments`,
                `trial`,
                `due_type`,
                `due_value`,
                `installments`
            ) VALUES (
                " . ($object->isEnabled()?1:0) . ",
                " . ($object->isSingle()?1:0) . ",
                '" . $object->getName() . "',
                '" . $object->getDescription() . "',
                " . ($object->isAcceptCreditCard()?1:0) . ",
                " . ($object->isAcceptBoleto()?1:0) . ",
                " . ($object->isAllowInstallments()?1:0) . ",                
                " . $object->getTrial() . ",
                '" . $object->getDueAt()->getType() . "',
                " . $object->getDueAt()->getValue() . ",
                '" . json_encode($object->getInstallments()) . "'
            )
        ";

        $this->db->query($query);

        $object->setId($this->db->getLastId());

        $this->createTemplateRepetitions($object);
    }

    protected function createTemplateRepetitions($template)
    {
        $templateRepetitionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TEMPLATE_REPETITION);

        $query = "
            INSERT INTO `" . $templateRepetitionTable . "` (
                `template_id`,
                `cycles`,
                `frequency`,
                `interval_type`,
                `discount_type`,
                `discount_value`
            ) VALUES 
        ";

        /** @var RepetitionValueObject $repetition */
        foreach ($template->getRepetitions() as $repetition) {
            $query .= "(
                ". $template->getId() .",
                ". $repetition->getCycles() . ",
                ". intval($repetition->getFrequency()) .",
                '". $repetition->getIntervalType() ."',
                '". $repetition->getDiscountType() ."',
                ". floatval($repetition->getDiscountValue()) ."
            ),";
        }
        $query = rtrim($query,',') . ';';

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        // TODO: Implement update() method.
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        // TODO: Implement findByMundipaggId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }
}