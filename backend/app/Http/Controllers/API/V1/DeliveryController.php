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
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'courier_id' => 'required|exists:couriers,id',
            ]);
            $delivery = $this->deliveryService->assignDelivery(
                $validated['order_id'],
                $validated['courier_id']
            );
            return response()->json([
                'message' => 'Delivery assigned successfully.',
                'delivery' => $delivery,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function accept(string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->acceptDelivery($id);
            return response()->json([
                'message' => 'Delivery accepted successfully.',
                'delivery' => $delivery,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function pickup(string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->pickupOrder($id);
            return response()->json([
                'message' => 'Order picked up successfully.',
                'delivery' => $delivery,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function inTransit(string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->inTransit($id);
            return response()->json([
                'message' => 'Delivery marked as in transit.',
                'delivery' => $delivery,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->completeDelivery(
                $id,
                $request->input('proof_image')
            );
            return response()->json([
                'message' => 'Delivery completed successfully.',
                'delivery' => $delivery,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function fail(Request $request, string $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->failDelivery(
                $id,
                $request->input('reason')
            );
            return response()->json([
                'message' => 'Delivery marked as failed.',
                'delivery' => $delivery,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $courier = $user->courier;

            if (!$courier) {
                return response()->json(['message' => 'Courier profile not found.'], 404);
            }

            $status = $request->query('status');
            $perPage = (int) $request->query('per_page', 15);
            $deliveries = $this->deliveryService->getDeliveries($courier->id, $status, $perPage);
            return response()->json($deliveries);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function earnings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $courier = $user->courier;

            if (!$courier) {
                return response()->json(['message' => 'Courier profile not found.'], 404);
            }

            $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->query('end_date', now()->toDateString());

            $earnings = $this->deliveryService->getEarnings($courier->id, $startDate, $endDate);
            return response()->json(['earnings' => $earnings]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
