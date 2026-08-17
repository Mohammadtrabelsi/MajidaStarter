<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    /**
     * SettingService::current
     */
    public function show(): JsonResponse
    {
        return response()->json($this->settings->current());
    }

    /**
     * SettingService::update
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_name' => ['nullable', 'array'],
            'site_name.*' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'array'],
            'site_description.*' => ['nullable', 'string'],
            'support_email' => ['nullable', 'email'],
            'maintenance_mode' => ['boolean'],
        ]);

        $setting = $this->settings->current();

        $data = [];

        if (array_key_exists('site_name', $validated)) {
            $data['site_name'] = $validated['site_name'];
        }

        if (array_key_exists('site_description', $validated)) {
            $data['site_description'] = $validated['site_description'];
        }

        if (array_key_exists('support_email', $validated)) {
            $data['support_email'] = $validated['support_email'] ?: null;
        }

        if (array_key_exists('maintenance_mode', $validated)) {
            $data['maintenance_mode'] = $validated['maintenance_mode'];
        }

        return response()->json($this->settings->update($setting, $data));
    }
}
