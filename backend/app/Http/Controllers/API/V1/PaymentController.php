<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\ProcessPaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function store(ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $payment = $this->paymentService->processPayment($data);
            return response()->json([
                'message' => 'Payment processed successfully.',
                'payment' => $payment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(string $orderId): JsonResponse
    {
        try {
            $payment = $this->paymentService->getPaymentByOrder($orderId);
            if (!$payment) {
                return response()->json(['message' => 'Payment not found.'], 404);
            }
            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function refund(Request $request, string $paymentId): JsonResponse
    {
        try {
            $payment = $this->paymentService->refund($paymentId, $request->input('reason'));
            return response()->json([
                'message' => 'Payment refunded successfully.',
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function verify(Request $request, string $transactionId): JsonResponse
    {
        try {
            $payment = $this->paymentService->verifyPayment($transactionId);
            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function history(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 15);
            $history = $this->paymentService->getPaymentHistory($request->user()->id, $perPage);
            return response()->json($history);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
