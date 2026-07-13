<?php

use App\Services\UserService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] #[Title('Admin Dashboard')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleAdmin(int $userId, UserService $users): void
    {
        $this->authorize('manage users');

        $target = \App\Models\User::findOrFail($userId);

        try {
            $users->toggleAdminRole(auth()->user(), $target);
        } catch (\DomainException $e) {
            $this->addError('toggle', $e->getMessage());
        }
    }

    #[Computed]
    public function stats(): array
    {
        return app(UserService::class)->stats();
    }

    #[Computed]
    public function users()
    {
        return app(UserService::class)->searchPaginated($this->search);
    }

    public function render()
    {
        return $this->view();
    }
};
?>

<div class="mx-auto max-w-6xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Admin Dashboard</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Overview of your application's users.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total users</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $this->stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Administrators</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $this->stats['admins'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">New this week</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $this->stats['newThisWeek'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">New today</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $this->stats['newToday'] }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-5 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Users</h2>

            <div class="relative w-full sm:w-72">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8" /><path stroke-linecap="round" d="M21 21l-4.35-4.35" /></svg>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search by name or email…"
                    class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-white/10 dark:bg-white/5 dark:text-white"
                >
            </div>
        </div>

        @error('toggle')
            <div class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                {{ $message }}
            </div>
        @enderror

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-white/10 dark:text-slate-400">
                        <th class="px-5 py-3 font-medium">User</th>
                        <th class="px-5 py-3 font-medium">Joined</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="transition hover:bg-slate-50 dark:hover:bg-white/5">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-xs font-semibold text-white">
                                        {{ Str::of($user->name)->substr(0, 2)->upper() }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="truncate text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $user->created_at->format('M j, Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $user->hasRole('admin') ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-300' }}">
                                    {{ $user->hasRole('admin') ? 'Admin' : 'Member' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @can('manage users')
                                    <button
                                        wire:click="toggleAdmin({{ $user->id }})"
                                        wire:confirm="Are you sure you want to {{ $user->hasRole('admin') ? 'remove admin access from' : 'grant admin access to' }} {{ $user->name }}?"
                                        @disabled($user->id === auth()->id())
                                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-white/10 dark:text-slate-200 dark:hover:bg-white/5"
                                    >
                                        {{ $user->hasRole('admin') ? 'Revoke admin' : 'Make admin' }}
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 p-5 dark:border-white/10">
            {{ $this->users->links() }}
        </div>
    </div>
</div>
