<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function findByTransactionId(string $transactionId)
    {
        return $this->model->where('transaction_id', $transactionId)->first();
    }

    public function getByOrder(string $orderId)
    {
        return $this->model->where('order_id', $orderId)->first();
    }

    public function complete(string $paymentId, array $gatewayResponse = [])
    {
        $payment = $this->findOrFail($paymentId);
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'gateway_response' => $gatewayResponse,
        ]);

        return $payment->fresh();
    }

    public function fail(string $paymentId, ?string $reason = null)
    {
        $payment = $this->findOrFail($paymentId);
        $payment->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);

        return $payment->fresh();
    }

    public function refund(string $paymentId, ?string $reason = null)
    {
        $payment = $this->findOrFail($paymentId);
        $payment->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_reason' => $reason,
        ]);

        return $payment->fresh();
    }
}