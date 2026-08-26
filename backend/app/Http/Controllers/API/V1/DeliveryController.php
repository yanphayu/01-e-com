<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        private readonly DeliveryService $deliveryService
    ) {}

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'courier_id' => 'required|exists:users,id',
            ]);
            $delivery = $this->deliveryService->assign($request->validated());
            return response()->json($delivery, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function accept(string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->accept($id);
            return response()->json($delivery);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function pickup(string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->pickup($id);
            return response()->json($delivery);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function inTransit(string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->inTransit($id);
            return response()->json($delivery);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->complete($id, $request->input('proof'));
            return response()->json($delivery);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function fail(Request $request, string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->fail($id, $request->input('reason'));
            return response()->json($delivery);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $deliveries = $this->deliveryService->list($request->user()->id, $request->user()->role, $request->query());
            return response()->json($deliveries);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function earnings(Request $request): JsonResponse
    {
        try {
            $earnings = $this->deliveryService->earnings($request->user()->id, $request->query());
            return response()->json($earnings);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
