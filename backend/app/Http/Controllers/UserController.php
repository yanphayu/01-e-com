<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| UserController
|--------------------------------------------------------------------------
| A controller receives HTTP requests from routes and decides how to respond.
| Flow: Browser -> Route (routes/api.php) -> Controller -> Database -> JSON response.
|
| This controller works with the "users" table in the PostgreSQL database
| through the User model (App\Models\User). It provides full CRUD:
|
|   GET    /api/users        -> index()   : list all users
|   POST   /api/users        -> store()   : create a new user
|   GET    /api/users/{id}   -> show()    : get one user
|   PUT    /api/users/{id}   -> update()  : edit one user
|   DELETE /api/users/{id}   -> destroy() : remove one user
*/

class UserController extends Controller
{
    // GET /api/users
    // Reads ALL rows from the "users" table and returns them as JSON.
    // Only selected columns are returned so password is never exposed.
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::select('id', 'name', 'email', 'email_verified_at', 'created_at')->get(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // POST /api/users
    // 1. Validates the incoming request data.
    // 2. Inserts a new row into the "users" table.
    // 3. Returns the created user with HTTP status 201.
    public function store(Request $request): JsonResponse
    {
        // Validation: if the rules fail, Laravel automatically returns a 422 error response.
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // INSERT INTO users (...) - the "hashed" cast on the model encrypts the password automatically.
        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user->only(['id', 'name', 'email', 'created_at']),
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    // GET /api/users/{id}
    // "User $user" uses Route Model Binding: Laravel finds the row by {id}
    // automatically; if not found it returns a 404 before this code runs.
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => $user->only(['id', 'name', 'email', 'email_verified_at', 'created_at']),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // PUT or PATCH /api/users/{id}
    // 1. Validates only the fields that were sent ("sometimes" = optional).
    // 2. Updates that row in the database.
    // The unique rule ignores this user's own email so updating without
    // changing the email does not trigger a "duplicate email" error.
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class . ',email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        // UPDATE users SET ... WHERE id = ?
        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user->only(['id', 'name', 'email', 'updated_at']),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // DELETE /api/users/{id}
    // Deletes the row from the "users" table.
    public function destroy(User $user): JsonResponse
    {
        // DELETE FROM users WHERE id = ?
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
