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

<div class="mx-auto max-w-6xl space-y-6" x-data="{ expanded: null }">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Activity Log</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Audit trail of changes made across the application.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-5 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <button
                    wire:click="$set('logName', '')"
                    class="rounded-full px-3 py-1 text-xs font-medium transition {{ $logName === '' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300' }}"
                >
                    All
                </button>
                @foreach ($logNames as $name)
                    <button
                        wire:click="$set('logName', '{{ $name }}')"
                        class="rounded-full px-3 py-1 text-xs font-medium capitalize transition {{ $logName === $name ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300' }}"
                    >
                        {{ $name }}
                    </button>
                @endforeach
            </div>

            <div class="relative w-full sm:w-72">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8" /><path stroke-linecap="round" d="M21 21l-4.35-4.35" /></svg>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search description…"
                    class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-white/10 dark:bg-white/5 dark:text-white"
                >
            </div>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-white/5">
            @forelse ($activities as $activity)
                <div wire:key="activity-{{ $activity->id }}" class="p-5">
                    <button
                        @click="expanded = expanded === {{ $activity->id }} ? null : {{ $activity->id }}"
                        class="flex w-full items-start justify-between gap-4 text-left"
                    >
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-xs font-semibold text-white">
                                {{ $activity->causer ? Str::of($activity->causer->name)->substr(0, 2)->upper() : '—' }}
                            </span>
                            <div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">
                                    {{ $activity->causer?->name ?? 'System' }}
                                    <span class="font-normal text-slate-500 dark:text-slate-400">{{ $activity->description }}</span>
                                    @if ($activity->subject)
                                        <span class="font-normal text-slate-500 dark:text-slate-400">— {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                                    {{ $activity->created_at->diffForHumans() }} · {{ $activity->created_at->format('M j, Y H:i') }}
                                    @if ($activity->log_name)
                                        · <span class="capitalize">{{ $activity->log_name }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($activity->properties->isNotEmpty())
                            <svg class="mt-1 h-4 w-4 shrink-0 text-slate-400 transition" :class="expanded === {{ $activity->id }} && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        @endif
                    </button>

                    @if ($activity->properties->isNotEmpty())
                        <div x-show="expanded === {{ $activity->id }}" x-collapse style="display: none;" class="mt-3 grid grid-cols-1 gap-3 rounded-lg bg-slate-50 p-4 text-xs dark:bg-white/5 sm:grid-cols-2">
                            @if ($activity->properties->has('old'))
                                <div>
                                    <p class="mb-1 font-semibold text-slate-500 dark:text-slate-400">Before</p>
                                    <pre class="overflow-x-auto whitespace-pre-wrap text-slate-700 dark:text-slate-300">{{ json_encode($activity->properties->get('old'), JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @endif
                            @if ($activity->properties->has('attributes'))
                                <div>
                                    <p class="mb-1 font-semibold text-slate-500 dark:text-slate-400">After</p>
                                    <pre class="overflow-x-auto whitespace-pre-wrap text-slate-700 dark:text-slate-300">{{ json_encode($activity->properties->get('attributes'), JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-10 text-center text-slate-500 dark:text-slate-400">No activity recorded yet.</div>
            @endforelse
        </div>

        <div class="border-t border-slate-200 p-5 dark:border-white/10">
            {{ $activities->links() }}
        </div>
    </div>
</div>
