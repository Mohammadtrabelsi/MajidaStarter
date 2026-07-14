<?php

namespace App\Models;

use App\Models\Concerns\TracksUserActions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\HasTranslations;

class Setting extends Model
{
    use HasTranslations, LogsActivity, TracksUserActions;

    protected $fillable = [
        'site_name',
        'site_description',
        'support_email',
        'maintenance_mode',
    ];

    public array $translatable = [
        'site_name',
        'site_description',
    ];

    protected $attributes = [
        'maintenance_mode' => false,
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['site_name', 'site_description', 'support_email', 'maintenance_mode'])
            ->logOnlyDirty()
            ->useLogName('settings');
    }
}
