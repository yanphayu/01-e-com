<?php

namespace App\Services;

use App\Models\Payment;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function processPayment(array $data): Payment
    {
        $order = $this->orderRepository->findOrFail($data['order_id']);

        if ($order->payment_status === 'paid') {
            throw new InvalidArgumentException('Order is already paid.');
        }

        $payment = $this->paymentRepository->create([
            'order_id' => $order->id,
            'user_id' => $order->buyer_id,
            'payment_method' => $data['payment_method'],
            'transaction_id' => 'TXN-' . Str::upper(Str::random(20)),
            'amount' => $order->total,
            'currency' => $data['currency'] ?? 'USD',
            'status' => 'pending',
        ]);

        $payment = $this->simulatePaymentProcessing($payment, $data['payment_method']);

        if ($payment->status === 'paid') {
            $this->orderRepository->update($order->id, [
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            $this->notificationRepository->createNotification(
                $order->buyer_id,
                'Payment Successful',
                "Payment for order #{$order->order_number} has been processed.",
                'payment',
                ['order_id' => $order->id, 'payment_id' => $payment->id]
            );

            $this->notificationRepository->createNotification(
                $order->seller_id,
                'Payment Received',
                "Payment for order #{$order->order_number} has been received.",
                'payment',
                ['order_id' => $order->id, 'payment_id' => $payment->id]
            );
        } else {
            $this->notificationRepository->createNotification(
                $order->buyer_id,
                'Payment Failed',
                "Payment for order #{$order->order_number} has failed.",
                'payment',
                ['order_id' => $order->id, 'payment_id' => $payment->id]
            );
        }

        return $payment;
    }

    public function getPaymentByOrder(string $orderId): ?Payment
    {
        return $this->paymentRepository->getByOrder($orderId);
    }

    public function refund(string $paymentId, ?string $reason = null): Payment
    {
        $payment = $this->paymentRepository->findOrFail($paymentId);

        if ($payment->status !== 'paid') {
            throw new InvalidArgumentException('Only completed payments can be refunded.');
        }

        $payment = $this->paymentRepository->refund($paymentId, $reason);

        $this->orderRepository->update($payment->order_id, [
            'payment_status' => 'refunded',
        ]);

        $this->notificationRepository->createNotification(
            $payment->user_id,
            'Payment Refunded',
            "Payment for order has been refunded.",
            'payment',
            ['order_id' => $payment->order_id, 'payment_id' => $payment->id]
        );

        return $payment;
    }

    public function verifyPayment(string $transactionId): Payment
    {
        $payment = $this->paymentRepository->findByTransactionId($transactionId);

        if (!$payment) {
            throw new InvalidArgumentException('Payment not found.');
        }

        return $payment;
    }

    public function getPaymentHistory(string $userId, int $perPage = 15)
    {
        return $this->paymentRepository->search(
            ['user_id' => $userId],
            $perPage
        );
    }

    protected function simulatePaymentProcessing(Payment $payment, string $method): Payment
    {
        $success = in_array($method, ['aba_payway', 'acleda', 'wing', 'bank_transfer', 'e_wallet', 'credit_card'])
            ? true
            : (rand(1, 10) > 2);

        if ($success) {
            return $this->paymentRepository->complete($payment->id, [
                'gateway' => 'simulated',
                'message' => 'Payment successful',
            ]);
        }

        return $this->paymentRepository->fail($payment->id, 'Simulated payment failure');
    }
}
