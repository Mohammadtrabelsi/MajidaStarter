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
    <div class="ms-page-head">
        <div>
            <h2>Users</h2>
            <p class="text-muted ms-note">{{ __('dashboard.users_description') }}</p>
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
            class="tag tag-accent ms-notice"
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
        <div class="tag tag-outline ms-notice">{{ $message }}</div>
    @enderror

    <div class="ms-toolbar">
        <div class="ms-search-field">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="rgba(var(--ink),.5)" stroke-width="1.5" class="ms-input-icon"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4-4"></path></svg>
            <input wire:model.live.debounce.300ms="search" class="input ms-pl-32" placeholder="Search by name or email…">
        </div>
    </div>

    <div class="card ms-p-0">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('dashboard.user') }}</th>
                        <th>{{ __('dashboard.joined') }}</th>
                        <th>{{ __('dashboard.role') }}</th>
                        <th class="ms-text-right">{{ __('dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td>
                                <div class="ms-row-10-center">
                                    <span class="avatar">{{ Str::of($user->name)->substr(0, 2)->upper() }}</span>
                                    <div class="ms-minw-0">
                                        <div class="ms-fw-500">{{ $user->name }}</div>
                                        <div class="text-muted ms-fs-12">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted ms-nowrap">{{ $user->created_at->format('M j, Y') }}</td>
                            <td>
                                <span class="tag {{ $user->hasRole('admin') ? 'tag-accent' : 'tag-neutral' }}">{{ $user->hasRole('admin') ? 'Admin' : 'Member' }}</span>
                            </td>
                            <td class="ms-text-right">
                                @can('manage users')
                                    <div class="ms-actions-end">
                                        <button
                                            type="button"
                                            wire:click="toggleAdmin({{ $user->id }})"
                                            wire:confirm="Are you sure you want to {{ $user->hasRole('admin') ? 'remove admin access from' : 'grant admin access to' }} {{ $user->name }}?"
                                            @disabled($user->id === auth()->id())
                                            class="btn ms-btn-sm"
                                        >
                                            {{ $user->hasRole('admin') ? __('dashboard.revoke_admin') : __('dashboard.make_admin') }}
                                        </button>
                                        <a
                                            href="{{ route('admin.users.edit', $user) }}"
                                            wire:navigate
                                            class="btn ms-btn-sm"
                                        >Edit</a>
                                        <button
                                            type="button"
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Permanently delete {{ $user->name }}? This cannot be undone."
                                            @disabled($user->id === auth()->id())
                                            class="btn ms-btn-sm-danger"
                                        >Delete</button>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted ms-empty">{{ __('dashboard.no_users_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ms-cell-top">
            {{ $this->users->links() }}
        </div>
    </div>

    {{-- Styles moved to css/admin-dashboard.css --}}
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
</div>
