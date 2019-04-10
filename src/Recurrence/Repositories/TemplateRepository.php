<?php

namespace Mundipagg\Core\Recurrence\Repositories;

use Mundipagg\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractRepository;
use Mundipagg\Core\Kernel\ValueObjects\AbstractValidString;
use Mundipagg\Core\Recurrence\Factories\TemplateFactory;

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

    protected function update(AbstractEntity &$object)
    {
        $templateTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TEMPLATE);

        $query = "
            UPDATE `" . $templateTable . "` SET
                `is_enabled` = " . ($object->isEnabled()?1:0) . ",
                `is_single` = " . ($object->isSingle()?1:0) . ",
                `name` = '" . $object->getName() . "',
                `description` = '" . $object->getDescription() . "',
                `accept_credit_card` = " . ($object->isAcceptCreditCard()?1:0) . ",
                `accept_boleto` = " . ($object->isAcceptBoleto()?1:0) . ",
                `allow_installments` = " . ($object->isAllowInstallments()?1:0) . ",
                `trial` = " . $object->getTrial() . ",
                `due_type` = '" . $object->getDueAt()->getType() . "',
                `due_value` = " . $object->getDueAt()->getValue() . ",
                `installments` = '" . json_encode($object->getInstallments()) . "'
            WHERE `id` = " . $object->getId() . "
        ";

        $this->db->query($query);

        $this->deleteTemplateRepetitions($object);
        $this->createTemplateRepetitions($object);
    }

    public function delete(AbstractEntity $object)
    {
        $templateTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TEMPLATE);

        $query = "
            UPDATE `" . $templateTable . "` SET
                `is_enabled` = false
             WHERE `id` = " . $object->getId() . "
        ";
        $this->db->query($query);

        return true;
    }

    public function find($objectId)
    {
        $templateTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TEMPLATE);
        $templateRepetitionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TEMPLATE_REPETITION);

        $query = "
             SELECT
              t.*,
              GROUP_CONCAT(r.frequency) AS frequency,
              GROUP_CONCAT(r.interval_type) AS interval_type,
              GROUP_CONCAT(r.discount_type) AS discount_type,
              GROUP_CONCAT(r.discount_value) AS discount_value,
              GROUP_CONCAT(r.cycles) AS cycles
            FROM `" . $templateTable . "` AS t
            INNER JOIN `" . $templateRepetitionTable . "` AS r
              ON t.id = r.template_id
            WHERE t.id = " . intval($objectId) . "
            GROUP BY t.id
        ";

        $result = $this->db->fetch($query . ";");
        if ($result->num_rows < 1 ) {
            return null;
        }

        return (new TemplateFactory())
            ->createFromDBData($result->rows[0]);
    }

    public function findByMundipaggId(AbstractValidString $mundipaggId)
    {
        // TODO: Implement findByMundipaggId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        $templateTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TEMPLATE);
        $templateRepetitionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TEMPLATE_REPETITION);

        $query = "
            SELECT
              t.*,
              GROUP_CONCAT(r.frequency) AS frequency,
              GROUP_CONCAT(r.interval_type) AS interval_type,
              GROUP_CONCAT(r.discount_type) AS discount_type,
              GROUP_CONCAT(r.discount_value) AS discount_value,
              GROUP_CONCAT(r.cycles) AS cycles
            FROM `" . $templateTable . "` AS t
            INNER JOIN `" . $templateRepetitionTable . "` AS r
              ON t.id = r.template_id
        ";

        if (!$listDisabled) {
            $query .= " WHERE t.is_enabled = true ";
        }

        $query .= " GROUP BY t.id";

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query . ";");

        $templateRootFactory = new TemplateFactory();
        $templateRoots = [];

        foreach ($result->rows as $row) {
            $templateRoot = $templateRootFactory->createFromDBData($row);
            $templateRoots[] = $templateRoot;
        }

        return $templateRoots;
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

    protected function deleteTemplateRepetitions($template)
    {
        $templateRepetitionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TEMPLATE_REPETITION);

        $this->db->query("
            DELETE FROM `" . $templateRepetitionTable . "` WHERE
                `template_id` = " . $template->getId() . "
        ");
    }
}