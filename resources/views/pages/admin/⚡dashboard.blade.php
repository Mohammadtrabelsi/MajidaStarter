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

    public function deleteUser(int $userId, UserService $users): void
    {
        $this->authorize('manage users');

        $target = \App\Models\User::findOrFail($userId);

        try {
            $users->delete(auth()->user(), $target);
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

<div>
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
        <div>
            <h2>Users</h2>
            <p class="text-muted" style="font-size: 13px; margin: 0;">{{ __('dashboard.users_description') }}</p>
        </div>
        @can('manage users')
            <a href="{{ route('admin.users.create') }}" wire:navigate class="btn btn-primary">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" d="M12 5v14M5 12h14"></path></svg>
                New user
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            class="tag tag-accent"
            style="display: block; margin-bottom: 16px; padding: 8px 12px;"
        >{{ session('status') }}</div>
    @endif

    <div class="stat-cards">
        @foreach ([
            ['Total users', $this->stats['total']],
            ['Administrators', $this->stats['admins']],
            ['New this week', $this->stats['newThisWeek']],
            ['New today', $this->stats['newToday']],
        ] as [$label, $value])
            <div class="card">
                <div class="card-kicker">{{ $label }}</div>
                <div class="stat-value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    @error('toggle')
        <div class="tag tag-outline" style="display: block; margin-bottom: 16px; padding: 8px 12px;">{{ $message }}</div>
    @enderror

    <div style="display: flex; align-items: center; gap: 12px; margin: 24px 0 16px; flex-wrap: wrap;">
        <div style="position: relative; flex: 1; min-width: 220px; max-width: 320px;">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="rgba(var(--ink),.5)" stroke-width="1.5" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%);"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4-4"></path></svg>
            <input wire:model.live.debounce.300ms="search" class="input" placeholder="Search by name or email…" style="padding-left: 32px;">
        </div>
    </div>

    <div class="card" style="padding: 0;">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('dashboard.user') }}</th>
                        <th>{{ __('dashboard.joined') }}</th>
                        <th>{{ __('dashboard.role') }}</th>
                        <th style="text-align: right;">{{ __('dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="avatar">{{ Str::of($user->name)->substr(0, 2)->upper() }}</span>
                                    <div style="min-width: 0;">
                                        <div style="font-weight: 500;">{{ $user->name }}</div>
                                        <div class="text-muted" style="font-size: 12px;">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted" style="white-space: nowrap;">{{ $user->created_at->format('M j, Y') }}</td>
                            <td>
                                <span class="tag {{ $user->hasRole('admin') ? 'tag-accent' : 'tag-neutral' }}">{{ $user->hasRole('admin') ? 'Admin' : 'Member' }}</span>
                            </td>
                            <td style="text-align: right;">
                                @can('manage users')
                                    <div style="display: inline-flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                                        <button
                                            type="button"
                                            wire:click="toggleAdmin({{ $user->id }})"
                                            wire:confirm="Are you sure you want to {{ $user->hasRole('admin') ? 'remove admin access from' : 'grant admin access to' }} {{ $user->name }}?"
                                            @disabled($user->id === auth()->id())
                                            class="btn"
                                            style="padding: 6px 12px; font-size: 12px;"
                                        >
                                            {{ $user->hasRole('admin') ? __('dashboard.revoke_admin') : __('dashboard.make_admin') }}
                                        </button>
                                        <a
                                            href="{{ route('admin.users.edit', $user) }}"
                                            wire:navigate
                                            class="btn"
                                            style="padding: 6px 12px; font-size: 12px;"
                                        >Edit</a>
                                        <button
                                            type="button"
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Permanently delete {{ $user->name }}? This cannot be undone."
                                            @disabled($user->id === auth()->id())
                                            class="btn"
                                            style="padding: 6px 12px; font-size: 12px; border-color: var(--color-accent-700); color: var(--color-accent-700);"
                                        >Delete</button>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted" style="padding: 28px; text-align: center;">{{ __('dashboard.no_users_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 12px 16px; border-top: 1px solid var(--color-divider);">
            {{ $this->users->links() }}
        </div>
    </div>

    {{-- Styles moved to css/admin-dashboard.css --}}
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
</div>
