<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    /**
     * Return the authenticated user.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('roles'));
    }

    /**
     * UserService::updateProfile
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        return response()->json($this->users->updateProfile($user, $validated));
    }

    /**
     * UserService::updatePassword
     */
    public function password(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->users->updatePassword($request->user(), $validated['password']);

        return response()->json(['message' => 'Password updated.']);
    }

    /**
     * UserService::deleteOwnAccount
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $this->users->deleteOwnAccount($request->user());

        return response()->json(null, 204);
    }
}
