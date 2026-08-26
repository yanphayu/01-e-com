<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
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
            $payment = $this->paymentService->process($request->validated());
            return response()->json($payment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(string $orderId): JsonResponse
    {
        try {
            $payment = $this->paymentService->getByOrderId($orderId);
            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function refund(Request $request, string $paymentId): JsonResponse
    {
        try {
            $payment = $this->paymentService->refund($paymentId, $request->input('reason'));
            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function verify(Request $request, string $transactionId): JsonResponse
    {
        try {
            $payment = $this->paymentService->verify($transactionId, $request->input('gateway_response'));
            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function history(Request $request): JsonResponse
    {
        try {
            $history = $this->paymentService->history($request->user()->id, $request->query());
            return response()->json($history);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
