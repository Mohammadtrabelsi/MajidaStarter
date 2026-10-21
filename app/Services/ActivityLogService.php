<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

/**
 * The filtered activity query shared by the activity-log screen and its export.
 */
class ActivityLogService
{
    /**
     * @return Collection<int, string>
     */
    public function logNames(): Collection
    {
        return Activity::query()->distinct()->pluck('log_name')->filter()->values();
    }

    /**
     * @return Builder<Activity>
     */
    public function query(?string $search = null, ?string $logName = null): Builder
    {
        return Activity::query()
            ->when($logName, fn ($query) => $query->where('log_name', $logName))
            ->when($search, fn ($query) => $query->where('description', 'like', "%{$search}%"));
    }
}
