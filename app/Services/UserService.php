<?php

namespace App\Services;

use App\Exceptions\DomainActionException;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        return $user;
    }

    public function attempt(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt(['email' => $email, 'password' => $password], $remember);
    }

    public function toggleAdminRole(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw new DomainActionException('You cannot change your own admin status.');
        }

        $target->hasRole('admin')
            ? $target->removeRole('admin')
            : $target->assignRole('admin');
    }

    public function create(array $data, array $roles = []): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles($roles);

        return $user;
    }

    public function updateUser(User $user, array $data, array $roles = []): User
    {
        if (array_key_exists('email', $data) && $data['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles($roles);

        return $user;
    }

    public function delete(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw new DomainActionException('You cannot delete your own account here.');
        }

        $target->delete();
    }

    public function updateProfile(User $user, array $data): User
    {
        $emailChanged = $data['email'] !== $user->email;

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }

    public function updatePassword(User $user, string $password): User
    {
        $user->password = Hash::make($password);
        $user->save();

        return $user;
    }

    public function deleteOwnAccount(User $user): void
    {
        Auth::logout();

        // Fully tear down the session (not just the auth state) so no stale
        // session/CSRF token survives the account deletion.
        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        $user->delete();
    }

    public function roleNames(): array
    {
        return Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function searchPaginated(?string $search, int $perPage = 8): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when($search, fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function stats(): array
    {
        // Collapse the three created_at counts into a single aggregate query
        // (the role-based admin count needs its own join, so it stays separate).
        $counts = User::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as new_this_week', [now()->subWeek()])
            ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as new_today', [now()->startOfDay()])
            ->toBase()
            ->first();

        return [
            'total' => (int) ($counts->total ?? 0),
            'admins' => User::role('admin')->count(),
            'newThisWeek' => (int) ($counts->new_this_week ?? 0),
            'newToday' => (int) ($counts->new_today ?? 0),
        ];
    }
}
