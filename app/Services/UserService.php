<?php

namespace App\Services;

use App\Models\User;
use DomainException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            throw new DomainException('You cannot change your own admin status.');
        }

        $target->hasRole('admin')
            ? $target->removeRole('admin')
            : $target->assignRole('admin');
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
            ->paginate($perPage);
    }

    public function stats(): array
    {
        return [
            'total' => User::count(),
            'admins' => User::role('admin')->count(),
            'newThisWeek' => User::where('created_at', '>=', now()->subWeek())->count(),
            'newToday' => User::whereDate('created_at', today())->count(),
        ];
    }
}
