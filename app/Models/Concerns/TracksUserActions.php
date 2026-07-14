<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Automatically stamps `created_by` / `updated_by` on models and exposes the
 * related creator / updater users. Combined with Eloquent's built-in
 * `created_at` / `updated_at` timestamps this gives every model a full audit
 * trail of who changed what and when, without any per-model boilerplate.
 */
trait TracksUserActions
{
    public static function bootTracksUserActions(): void
    {
        static::creating(function (Model $model): void {
            $userId = Auth::id();

            if ($userId === null) {
                return;
            }

            if (empty($model->getAttribute('created_by'))) {
                $model->setAttribute('created_by', $userId);
            }

            if (empty($model->getAttribute('updated_by'))) {
                $model->setAttribute('updated_by', $userId);
            }
        });

        static::updating(function (Model $model): void {
            $userId = Auth::id();

            if ($userId !== null && ! $model->isDirty('updated_by')) {
                $model->setAttribute('updated_by', $userId);
            }
        });
    }

    /**
     * The user who created the record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last updated the record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
