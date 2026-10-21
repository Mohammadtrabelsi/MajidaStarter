<?php

use App\Models\User;
use App\Services\SystemHealthService;
use App\Services\UserService;
use App\Services\VisitService;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

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
    public function signups(): array
    {
        return app(UserService::class)->dailySignups(14);
    }

    #[Computed]
    public function newThisMonth(): int
    {
        return app(UserService::class)->newThisMonth();
    }

    #[Computed]
    public function roleSlices(): array
    {
        $rows = app(UserService::class)->roleDistribution();
        $total = max(1, array_sum(array_column($rows, 'count')));
        $palette = ['var(--color-navy)', 'var(--color-accent)', 'var(--color-teal-light)', 'var(--color-orange)', 'rgba(var(--ink), .35)'];
        $offset = 0.0;

        return collect($rows)->values()->map(function (array $row, int $i) use ($total, $palette, &$offset) {
            $share = $row['count'] / $total * 100;
            $slice = [
                'label' => $row['role'] === null ? __('dashboard.no_role') : Str::headline($row['role']),
                'count' => $row['count'],
                'percent' => (int) round($share),
                'length' => round($share, 3),
                'offset' => round(-$offset, 3),
                'color' => $palette[$i % count($palette)],
            ];
            $offset += $share;

            return $slice;
        })->all();
    }

    #[Computed]
    public function recentActivity()
    {
        if (! auth()->user()->can('view activity log')) {
            return collect();
        }

        return Activity::query()->with('causer')->latest()->latest('id')->limit(4)->get();
    }

    #[Computed]
    public function health(): array
    {
        return app(SystemHealthService::class)->checks();
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
            <h2 class="ms-mb-0">{{ __('dashboard.overview_title') }}</h2>
            <p class="text-muted ms-overview-sub">{{ __('dashboard.overview_subtitle') }}</p>
        </div>
        <div class="ms-row-10 ms-wrap">
            @can('manage users')
                <a href="{{ route('admin.users.create') }}" wire:navigate class="btn btn-primary">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"></circle><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M19 8v6M16 11h6"></path></svg>
                    New user
                    @if ($this->stats['newToday'] > 0)
                        <span class="btn-badge" title="{{ $this->stats['newToday'] }} new today">{{ $this->stats['newToday'] }}</span>
                    @endif
                </a>
            @endcan
            @can('view activity log')
                <a href="{{ route('admin.activity-log') }}" wire:navigate class="btn">{{ __('dashboard.view_activity_logs') }}</a>
            @endcan
        </div>
    </div>

    <div class="ov-grid">
        <section class="card ov-users">
            <h3 class="ov-title">{{ __('dashboard.users_overview') }}</h3>
            <div class="stat-value ov-big">{{ number_format($this->newThisMonth) }}</div>
            <div class="text-muted ms-fs-13">{{ __('dashboard.new_users_month') }}</div>
            @php($peak = max(1, max(array_column($this->signups, 'count'))))
            <div class="ov-bars" role="img" aria-label="{{ __('dashboard.signups_chart', ['days' => count($this->signups)]) }}">
                @foreach ($this->signups as $i => $day)
                    <span
                        class="ov-bar {{ $i % 3 === 1 ? 'is-accent' : '' }}"
                        style="--h: {{ max(4, round($day['count'] / $peak * 100)) }}%"
                        title="{{ $day['date']->format('M j') }}: {{ $day['count'] }}"
                    ></span>
                @endforeach
            </div>
        </section>

        <section class="card ov-activity">
            <h3 class="ov-title">{{ __('dashboard.activity_snippet') }}</h3>
            <ul class="ov-list">
                @forelse ($this->recentActivity as $activity)
                    <li class="ov-item" wire:key="act-{{ $activity->id }}">
                        <span class="ov-icon {{ $activity->causer ? 'is-accent' : '' }}" aria-hidden="true">
                            @if ($activity->causer)
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"></path></svg>
                            @else
                                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 12a9 9 0 1 0 3-6.7"></path><path d="M3 4v5h5"></path><path d="M12 8v4l3 2"></path></svg>
                            @endif
                        </span>
                        <span class="ms-minw-0 ms-flex-1">
                            <span class="ms-block ov-item-text">
                                <strong class="ms-fw-500">{{ $activity->causer?->name ?? 'System' }}</strong>
                                {{ $activity->description }}
                                @if ($activity->subject_type)
                                    <span class="text-muted">· {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</span>
                                @endif
                            </span>
                            <span class="ms-block text-muted ms-fs-12">{{ $activity->created_at?->diffForHumans() }}</span>
                        </span>
                    </li>
                @empty
                    <li class="text-muted ms-fs-13">{{ __('dashboard.no_activity') }}</li>
                @endforelse
            </ul>
        </section>

        <section class="card ov-roles">
            <h3 class="ov-title">{{ __('dashboard.roles_distribution') }}</h3>
            <div class="ov-donut-row">
                <svg class="ov-donut" viewBox="0 0 120 120" role="img" aria-label="{{ collect($this->roleSlices)->map(fn ($s) => $s['label'].' '.$s['percent'].'%')->implode(', ') }}">
                    <g transform="rotate(-90 60 60)" fill="none" stroke-width="30">
                        <circle cx="60" cy="60" r="40" stroke="rgba(var(--ink), .08)"></circle>
                        @foreach ($this->roleSlices as $slice)
                            <circle cx="60" cy="60" r="40" pathLength="100" stroke="{{ $slice['color'] }}" stroke-dasharray="{{ $slice['length'] }} {{ 100 - $slice['length'] }}" stroke-dashoffset="{{ $slice['offset'] }}"></circle>
                        @endforeach
                    </g>
                </svg>
                <ul class="ov-legend">
                    @foreach ($this->roleSlices as $slice)
                        <li>
                            <span class="ov-swatch" style="background: {{ $slice['color'] }}"></span>
                            <span class="ms-flex-1">{{ $slice['label'] }}</span>
                            <span class="text-muted">{{ $slice['percent'] }}%</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="card ov-health">
            <h3 class="ov-title">{{ __('dashboard.system_health') }}</h3>
            <ul class="ov-list">
                @foreach ($this->health as $check)
                    <li class="ov-health-row">
                        @if ($check['ok'])
                            <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><circle cx="12" cy="12" r="11" fill="var(--color-accent)"></circle><path d="M7 12.5l3.2 3L17 9" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        @else
                            <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><circle cx="12" cy="12" r="11" fill="var(--color-orange)"></circle><path d="M12 7v6M12 16.5v.5" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round"></path></svg>
                        @endif
                        <span class="ms-flex-1">
                            {{ __('dashboard.health_'.$check['key']) }}
                            @if ($check['detail'])
                                <span class="text-muted ms-fs-12">· {{ $check['detail'] }}</span>
                            @endif
                        </span>
                        <span class="{{ $check['ok'] ? 'ov-ok' : 'ov-bad' }}">{{ $check['ok'] ? __('dashboard.operational') : __('dashboard.degraded') }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <div class="ms-page-head ov-users-head" id="users">
        <div>
            <h2 class="ms-mb-0">{{ __('app.users') }}</h2>
            <p class="text-muted ms-note">{{ __('dashboard.users_description') }}</p>
        </div>
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
</div>
