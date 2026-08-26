<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\CreateOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['buyer_id'] = $request->user()->id;
            $order = $this->orderService->create($data);
            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $status = $request->query('status');
            $perPage = (int) $request->query('per_page', 15);

            if ($user->isSeller()) {
                $sellerProfile = $user->sellerProfile;
                if (!$sellerProfile) {
                    return response()->json(['message' => 'Seller profile not found.'], 404);
                }
                $orders = $this->orderService->getBySeller($sellerProfile->id, $status, $perPage);
            } elseif ($user->isCourier()) {
                $courier = $user->courier;
                if (!$courier) {
                    return response()->json(['message' => 'Courier profile not found.'], 404);
                }
                $orders = $this->orderService->getByBuyer($user->id, $status, $perPage);
            } else {
                $orders = $this->orderService->getByBuyer($user->id, $status, $perPage);
            }

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->getById($id);
            return response()->json($order->load(['items', 'payment', 'delivery', 'buyer', 'seller', 'seller.user']));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function accept(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->acceptOrder($id);
            return response()->json([
                'message' => 'Order accepted successfully.',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->rejectOrder($id);
            return response()->json([
                'message' => 'Order rejected successfully.',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function pack(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->packOrder($id);
            return response()->json([
                'message' => 'Order packed successfully.',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function ship(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->shipOrder($id);
            return response()->json([
                'message' => 'Order shipped successfully.',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function deliver(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->deliverOrder($id);
            return response()->json([
                'message' => 'Order marked as delivered.',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        try {
            $order = $this->orderService->cancelOrder($id, $request->input('reason'));
            return response()->json([
                'message' => 'Order cancelled successfully.',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $sellerProfile = $user->sellerProfile;

            if (!$sellerProfile) {
                return response()->json(['message' => 'Seller profile not found.'], 404);
            }

            $stats = $this->orderService->getDashboardStats($sellerProfile->id);
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function revenueReport(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $sellerProfile = $user->sellerProfile;

            if (!$sellerProfile) {
                return response()->json(['message' => 'Seller profile not found.'], 404);
            }

            $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->query('end_date', now()->toDateString());

            $report = $this->orderService->getRevenueReport($startDate, $endDate, $sellerProfile->id);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
