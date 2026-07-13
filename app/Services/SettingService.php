<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function current(): Setting
    {
        return Setting::query()->firstOrCreate([], [
            'site_name' => ['en' => config('app.name')],
            'site_description' => ['en' => ''],
        ]);
    }

    public function update(Setting $setting, array $data): Setting
    {
        $setting->fill($data);
        $setting->save();

        return $setting;
    }
}
