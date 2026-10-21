<?php

use App\Models\User;
use App\Services\UserService;
use App\Services\VisitService;
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

    /** all | admin | member */
    #[Url(as: 'role', history: true)]
    public string $role = 'all';

    /** active | trashed */
    #[Url(as: 'view', history: true)]
    public string $listing = 'active';

    /** name | created_at */
    #[Url(as: 'sort', history: true)]
    public string $sort = 'created_at';

    #[Url(as: 'dir', history: true)]
    public string $direction = 'desc';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'role', 'listing', 'sort', 'direction'], true)) {
            $this->resetPage();
        }
    }

    public function sortBy(string $column): void
    {
        $this->direction = $this->sort === $column && $this->direction === 'asc' ? 'desc' : 'asc';
        $this->sort = $column;
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function toggleAdmin(int $userId, UserService $users): void
    {
        $this->authorize('manage users');

        $target = User::findOrFail($userId);

        try {
            $users->toggleAdminRole(auth()->user(), $target);
        } catch (\DomainException $e) {
            $this->addError('toggle', $e->getMessage());
        }
    }

    public function deleteUser(int $userId, UserService $users): void
    {
        $this->authorize('manage users');

        $target = User::findOrFail($userId);

        try {
            $users->delete(auth()->user(), $target);
        } catch (\DomainException $e) {
            $this->addError('toggle', $e->getMessage());
        }
    }

    public function restoreUser(int $userId, UserService $users): void
    {
        $this->authorize('manage users');

        $users->restore(User::onlyTrashed()->findOrFail($userId));
    }

    public function forceDeleteUser(int $userId, UserService $users): void
    {
        $this->authorize('manage users');

        try {
            $users->forceDelete(auth()->user(), User::onlyTrashed()->findOrFail($userId));
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
    public function visits(): array
    {
        return app(VisitService::class)->stats();
    }

    #[Computed]
    public function users()
    {
        return app(UserService::class)->searchPaginated($this->search, 8, [
            'role' => in_array($this->role, ['admin', 'member'], true) ? $this->role : null,
            'trashed' => $this->listing === 'trashed' ? 'only' : null,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ]);
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
        <div data-autohide="3000" class="tag tag-accent ms-notice">{{ session('status') }}</div>
    @endif

    <div class="stat-cards">
        @foreach ([
            ['Total users', $this->stats['total']],
            ['Administrators', $this->stats['admins']],
            ['New this week', $this->stats['newThisWeek']],
            ['New today', $this->stats['newToday']],
            ['Trashed users', $this->stats['trashed']],
        ] as [$label, $value])
            <div class="card">
                <div class="card-kicker">{{ $label }}</div>
                <div class="stat-value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="stat-cards">
        @foreach ([
            ['Visits today', $this->visits['today'], $this->visits['uniqueToday'].' unique'],
            ['Visits this week', $this->visits['week'], $this->visits['uniqueWeek'].' unique'],
            ['Visits (30 days)', $this->visits['month'], 'Last 30 days'],
            ['Visits all time', $this->visits['total'], 'All time'],
        ] as [$label, $value, $hint])
            <div class="card">
                <div class="card-kicker">{{ $label }}</div>
                <div class="stat-value">{{ number_format($value) }}</div>
                <div class="text-muted ms-fs-12">{{ $hint }}</div>
            </div>
        @endforeach
    </div>

    @error('toggle')
        <div class="tag tag-outline ms-notice">{{ $message }}</div>
    @enderror

    <div class="ms-toolbar">
        <div class="ms-search-field">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="rgba(var(--ink),.5)" stroke-width="1.5" class="ms-input-icon"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4-4"></path></svg>
            <input wire:model.live.debounce.300ms="search" class="input ms-pl-32" placeholder="{{ __('dashboard.search_placeholder') }}">
        </div>

        <div class="ms-row-10">
            <select wire:model.live="listing" class="input" aria-label="View">
                <option value="active">Active users</option>
                <option value="trashed">Trashed users</option>
            </select>
            <select wire:model.live="role" class="input" aria-label="Role">
                <option value="all">{{ __('dashboard.all_roles') }}</option>
                <option value="admin">Administrators</option>
                <option value="member">{{ __('dashboard.member') }}</option>
            </select>
        </div>
    </div>

    <div class="card ms-p-0">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <button type="button" wire:click="sortBy('name')" class="ms-sort-btn">{{ __('dashboard.user') }}</button>
                        </th>
                        <th>
                            <button type="button" wire:click="sortBy('created_at')" class="ms-sort-btn">{{ __('dashboard.joined') }}</button>
                        </th>
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
                                @if ($user->trashed())
                                    <span class="tag tag-outline">Trashed</span>
                                @else
                                    <span class="tag {{ $user->hasRole('admin') ? 'tag-accent' : 'tag-neutral' }}">{{ $user->hasRole('admin') ? 'Admin' : 'Member' }}</span>
                                @endif
                            </td>
                            <td class="ms-text-right">
                                @can('manage users')
                                    <div class="ms-actions-end">
                                        @if ($user->trashed())
                                            <button type="button" wire:click="restoreUser({{ $user->id }})" class="btn ms-btn-sm">Restore</button>
                                            <button
                                                type="button"
                                                wire:click="forceDeleteUser({{ $user->id }})"
                                                wire:confirm="Permanently delete {{ $user->name }}? This cannot be undone."
                                                @disabled($user->id === auth()->id())
                                                class="btn ms-btn-sm-danger"
                                            >Delete permanently</button>
                                        @else
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
                                                wire:confirm="Move {{ $user->name }} to the trash? They will not be able to sign in until restored."
                                                @disabled($user->id === auth()->id())
                                                class="btn ms-btn-sm-danger"
                                            >Move to trash</button>
                                        @endif
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
