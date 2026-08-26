<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
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
            $order = $this->orderService->create($request->validated());
            return response()->json($order, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $orders = $user->role === 'seller'
                ? $this->orderService->getSellerOrders($user->id, $request->query())
                : $this->orderService->getBuyerOrders($user->id, $request->query());
            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->findById($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function accept(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->accept($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function reject(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->reject($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function pack(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->pack($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function ship(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->ship($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deliver(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->deliver($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        try {
            $order = $this->orderService->cancel($id, $request->user()->id, $request->input('reason'));
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function dashboard(Request $request): JsonResponse
    {
        try {
            $stats = $this->orderService->sellerDashboard($request->user()->id, $request->query());
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function revenueReport(Request $request): JsonResponse
    {
        try {
            $report = $this->orderService->revenueReport($request->user()->id, $request->query());
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
