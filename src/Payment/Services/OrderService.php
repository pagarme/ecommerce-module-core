<?php

namespace Mundipagg\Core\Payment\Services;

use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;

class OrderService
{
    public function getPixQrCodeInfoFromOrder(OrderId $orderId)
    {
        $orderRepository = new OrderRepository();
        $order = $orderRepository->findByMundipaggId(new OrderId($orderId));
        $qrCodeInfo = [];

        if ($order !== null) {
            $charges = $order->getCharges();
            foreach ($charges as $charge) {
                $qrCodeInfo = $this->getInfoFromCharge($charge);
            }
        }

        return $qrCodeInfo;
    }

    private function getInfoFromCharge($charge)
    {
        $transaction = $charge->getLastTransaction();
        $postData = $transaction->getPostData();
        $data = json_decode($postData->tran_data, true);
        if (!empty($data['qr_code']) && !empty($data['qr_code_url'])) {
            $qrCodeInfo['qr_code'] = $data['qr_code'];
            $qrCodeInfo['qr_code_url'] = $data['qr_code_url'];
            return $qrCodeInfo;
        }

        return [];
    }
}