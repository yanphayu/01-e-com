<?php

namespace App\Repositories\Interfaces;

interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByTransactionId(string $transactionId);
    public function getByOrder(string $orderId);
    public function complete(string $paymentId, array $gatewayResponse = []);
    public function fail(string $paymentId, ?string $reason = null);
    public function refund(string $paymentId, ?string $reason = null);
}
