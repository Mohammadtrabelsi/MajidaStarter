<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    /**
     * UserService::searchPaginated
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json(
            $this->users->searchPaginated(
                $validated['search'] ?? null,
                $validated['per_page'] ?? 8,
            )
        );
    }

    /**
     * UserService::roleNames
     */
    public function roles(): JsonResponse
    {
        return response()->json($this->users->roleNames());
    }

    /**
     * UserService::stats
     */
    public function stats(): JsonResponse
    {
        return response()->json($this->users->stats());
    }

    /**
     * UserService::create
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::in($this->users->roleNames())],
        ]);

        $user = $this->users->create($validated, $validated['roles'] ?? []);

        return response()->json($user->load('roles'), 201);
    }

    /**
     * UserService::updateUser
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::in($this->users->roleNames())],
        ]);

        $user = $this->users->updateUser($user, $validated, $validated['roles'] ?? []);

        return response()->json($user->load('roles'));
    }

    /**
     * UserService::delete
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        try {
            $this->users->delete($request->user(), $user);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(null, 204);
    }

    /**
     * UserService::toggleAdminRole
     */
    public function toggleAdmin(Request $request, User $user): JsonResponse
    {
        try {
            $this->users->toggleAdminRole($request->user(), $user);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($user->fresh()->load('roles'));
    }
}
