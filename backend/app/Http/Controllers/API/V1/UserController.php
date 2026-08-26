<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuspendUserRequest;
use App\Http\Requests\Buyer\UpdateProfileRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected readonly UserService $userService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name', 'email', 'status', 'role']);
            $perPage = $request->input('per_page', 15);

            $users = $this->userService->getAll($filters, $perPage);

            return response()->json($users, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $user = $this->userService->getById($id);

            return response()->json($user, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->updateProfile(
                $request->user(),
                $request->validated(),
            );

            return response()->json([
                'message' => 'Profile updated successfully.',
                'user' => $user,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function suspend(string $id, SuspendUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->suspend($id, $request->input('reason'));

            return response()->json([
                'message' => 'User suspended successfully.',
                'user' => $user,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function unsuspend(string $id): JsonResponse
    {
        try {
            $user = $this->userService->unsuspend($id);

            return response()->json([
                'message' => 'User unsuspended successfully.',
                'user' => $user,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function dashboard(): JsonResponse
    {
        try {
            $stats = $this->userService->getDashboardStats();

            return response()->json($stats, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
