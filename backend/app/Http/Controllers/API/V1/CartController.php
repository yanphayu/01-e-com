<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\AddToCartRequest;
use App\Http\Requests\Buyer\UpdateCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected readonly CartService $cartService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $cart = $this->cartService->getCart($request->user()->id);

            return response()->json($cart, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function addItem(AddToCartRequest $request): JsonResponse
    {
        try {
            $cart = $this->cartService->addItem(
                $request->user()->id,
                $request->input('product_id'),
                $request->input('quantity', 1),
            );

            return response()->json([
                'message' => 'Item added to cart.',
                'cart' => $cart,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateItem(UpdateCartItemRequest $request, string $cartItemId): JsonResponse
    {
        try {
            $cart = $this->cartService->updateItem(
                $request->user()->id,
                $cartItemId,
                $request->input('quantity'),
            );

            return response()->json([
                'message' => 'Cart item updated.',
                'cart' => $cart,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function removeItem(string $cartItemId, Request $request): JsonResponse
    {
        try {
            $cart = $this->cartService->removeItem(
                $request->user()->id,
                $cartItemId,
            );

            return response()->json([
                'message' => 'Item removed from cart.',
                'cart' => $cart,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function clear(Request $request): JsonResponse
    {
        try {
            $cart = $this->cartService->clearCart($request->user()->id);

            return response()->json([
                'message' => 'Cart cleared successfully.',
                'cart' => $cart,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
