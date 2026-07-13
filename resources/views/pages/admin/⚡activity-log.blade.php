<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

new #[Layout('layouts::app')] #[Title('Activity Log')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'log', history: true)]
    public string $logName = '';

    public function mount(): void
    {
        $this->authorize('view activity log');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLogName(): void
    {
        $this->resetPage();
    }

    public function logNames()
    {
        return Activity::query()->distinct()->pluck('log_name')->filter()->values();
    }

    public function activities()
    {
        return Activity::query()
            ->with(['causer', 'subject'])
            ->when($this->logName, fn ($query) => $query->where('log_name', $this->logName))
            ->when($this->search, fn ($query) => $query->where('description', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return $this->view([
            'activities' => $this->activities(),
            'logNames' => $this->logNames(),
        ]);
    }
};
?>

<div x-data="{ expanded: null }">
    <div style="margin-bottom: 24px;">
        <h2>Activity log</h2>
        <p class="text-muted" style="font-size: 13px; margin: 0;">Audit trail of changes made across the application.</p>
    </div>

    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
        <div style="display: inline-flex; border: 1px solid var(--color-divider); flex-wrap: wrap;">
            <div wire:click="$set('logName', '')" style="padding: 7px 12px; font-size: 12px; cursor: pointer; {{ $logName === '' ? 'background: var(--color-accent); color: var(--color-bg);' : '' }}">
                All
            </div>
            @foreach ($logNames as $name)
                <div wire:click="$set('logName', '{{ $name }}')" style="padding: 7px 12px; font-size: 12px; cursor: pointer; text-transform: capitalize; {{ $logName === $name ? 'background: var(--color-accent); color: var(--color-bg);' : '' }}">
                    {{ $name }}
                </div>
            @endforeach
        </div>

        <div style="position: relative; margin-left: auto; flex: 1; min-width: 220px; max-width: 300px;">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="rgba(var(--ink),.5)" stroke-width="1.5" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%);"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4-4"></path></svg>
            <input wire:model.live.debounce.300ms="search" class="input" placeholder="Search description…" style="padding-left: 32px;">
        </div>
    </div>

    <div class="card" style="padding: 0;">
        @forelse ($activities as $activity)
            <div wire:key="activity-{{ $activity->id }}" style="padding: 16px 20px; border-bottom: 1px solid rgba(var(--ink), 0.08);">
                <button
                    type="button"
                    @click="expanded = expanded === {{ $activity->id }} ? null : {{ $activity->id }}"
                    style="display: flex; width: 100%; align-items: flex-start; justify-content: space-between; gap: 16px; background: transparent; border: none; cursor: pointer; text-align: left; font: inherit; color: inherit;"
                >
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <span class="avatar">{{ $activity->causer ? Str::of($activity->causer->name)->substr(0, 2)->upper() : '—' }}</span>
                        <div>
                            <div style="font-size: 14px;">
                                <span style="font-weight: 500;">{{ $activity->causer?->name ?? 'System' }}</span>
                                <span class="text-muted">{{ $activity->description }}</span>
                                @if ($activity->subject)
                                    <span class="text-muted">— {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</span>
                                @endif
                            </div>
                            <div class="text-muted" style="font-size: 11px; margin-top: 2px;">
                                {{ $activity->created_at->diffForHumans() }} · {{ $activity->created_at->format('M j, Y H:i') }}
                                @if ($activity->log_name)
                                    · <span style="text-transform: capitalize;">{{ $activity->log_name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($activity->properties->isNotEmpty())
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="rgba(var(--ink),.5)" stroke-width="1.75" style="margin-top: 4px; flex: none; transition: transform 0.15s;" :style="expanded === {{ $activity->id }} ? 'transform: rotate(180deg)' : ''"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                    @endif
                </button>

                @if ($activity->properties->isNotEmpty())
                    <div x-show="expanded === {{ $activity->id }}" x-collapse style="display: none;" >
                        <div class="prop-grid" style="margin-top: 12px; padding: 14px; background: var(--color-surface); border: 1px solid var(--color-divider); font-size: 12px;">
                            @if ($activity->properties->has('old'))
                                <div>
                                    <div class="card-kicker">Before</div>
                                    <pre style="margin: 0; overflow-x: auto; white-space: pre-wrap; font-size: 12px;">{{ json_encode($activity->properties->get('old'), JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @endif
                            @if ($activity->properties->has('attributes'))
                                <div>
                                    <div class="card-kicker">After</div>
                                    <pre style="margin: 0; overflow-x: auto; white-space: pre-wrap; font-size: 12px;">{{ json_encode($activity->properties->get('attributes'), JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-muted" style="padding: 40px; text-align: center;">No activity recorded yet.</div>
        @endforelse

        <div style="padding: 12px 16px; border-top: 1px solid var(--color-divider);">
            {{ $activities->links() }}
        </div>
    </div>

    <style>
        .prop-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        @media (max-width: 640px) { .prop-grid { grid-template-columns: 1fr; } }
    </style>
</div>
