<?php

namespace Pagarme\Core\Kernel\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Factories\OrderFactory;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

final class OrderRepository extends AbstractRepository
{
    /**
     *
     * @param  Order $object
     * @throws \Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $order = json_decode(json_encode($object));

        $query = "
          INSERT INTO $orderTable (`pagarme_id`, `code`, `status`)
          VALUES (:pagarme_id, :code, :status);
         ";

        $this->db->query($query, [
            'pagarme_id' => $order->pagarmeId,
            'code' => $order->code,
            'status' => $order->status,
        ]);

        $chargeRepository = new ChargeRepository();
        foreach ($object->getCharges() as $charge) {
            $chargeRepository->save($charge);
            $object->updateCharge($charge, true);
        }
    }

    /**
     *
     * @param  Order $object
     * @throws \Exception
     */
    protected function update(AbstractEntity &$object)
    {
        $order = json_decode(json_encode($object));
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "
            UPDATE $orderTable SET
              status = :status
            WHERE id = :id
        ";

        $this->db->query($query, [
            'status' => $order->status,
            'id' => $order->id,
        ]);

        //update Charges;
        $chargeRepository = new ChargeRepository();
        foreach ($object->getCharges() as $charge) {
            $chargeRepository->save($charge);
            $object->updateCharge($charge, true);
        }
    }

    public function findByCode($codeId)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);
        $codeId = filter_var($codeId, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE code = :code;";

        $result = $this->db->fetch($query, ['code' => $codeId]);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    /**
     * @param AbstractValidString $pagarmeId
     * @return Order|null
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $id = $pagarmeId->getValue();
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE pagarme_id = :pagarme_id;";

        $result = $this->db->fetch($query, ['pagarme_id' => $id]);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function findByPlatformId($platformID)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);
        $platformID = filter_var($platformID, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE code = :code ORDER BY id DESC;";

        $result = $this->db->fetch($query, ['code' => $platformID]);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }
}
