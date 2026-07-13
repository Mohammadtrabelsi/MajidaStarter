<?php

use App\Services\SettingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Settings')] class extends Component
{
    public string $activeLocale = 'en';

    public array $siteName = [];

    public array $siteDescription = [];

    #[Validate('nullable|email')]
    public string $supportEmail = '';

    public bool $maintenanceMode = false;

    public array $locales = ['en' => 'English', 'ar' => 'العربية'];

    public function mount(SettingService $settings): void
    {
        $this->authorize('manage settings');

        $setting = $settings->current();

        foreach (array_keys($this->locales) as $locale) {
            $this->siteName[$locale] = $setting->getTranslation('site_name', $locale, false) ?? '';
            $this->siteDescription[$locale] = $setting->getTranslation('site_description', $locale, false) ?? '';
        }

        $this->supportEmail = $setting->support_email ?? '';
        $this->maintenanceMode = $setting->maintenance_mode;
    }

    public function save(SettingService $settings): void
    {
        $this->validate();

        $setting = $settings->current();

        foreach (array_keys($this->locales) as $locale) {
            $setting->setTranslation('site_name', $locale, $this->siteName[$locale] ?? '');
            $setting->setTranslation('site_description', $locale, $this->siteDescription[$locale] ?? '');
        }

        $settings->update($setting, [
            'support_email' => $this->supportEmail ?: null,
            'maintenance_mode' => $this->maintenanceMode,
        ]);

        $this->dispatch('settings-saved');
    }
};
?>

<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Settings</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage general application and localization settings.</p>
    </div>

    <div
        x-data="{ show: false }"
        x-on:settings-saved.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        style="display: none;"
        class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400"
    >
        Settings saved successfully.
    </div>

    <form wire:submit="save" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <div>
            <div class="mb-4 flex gap-1 border-b border-slate-200 dark:border-white/10">
                @foreach ($locales as $code => $label)
                    <button
                        type="button"
                        wire:click="$set('activeLocale', '{{ $code }}')"
                        class="border-b-2 px-3 py-2 text-sm font-medium transition {{ $activeLocale === $code ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @foreach ($locales as $code => $label)
                <div class="space-y-4" @if ($activeLocale !== $code) style="display: none;" @endif>
                    <div>
                        <x-input-label :for="'siteName-'.$code" :value="'Site name ('.$label.')'" />
                        <x-text-input
                            wire:model="siteName.{{ $code }}"
                            id="siteName-{{ $code }}"
                            type="text"
                            :dir="$code === 'ar' ? 'rtl' : 'ltr'"
                        />
                    </div>

                    <div>
                        <x-input-label :for="'siteDescription-'.$code" :value="'Site description ('.$label.')'" />
                        <textarea
                            wire:model="siteDescription.{{ $code }}"
                            id="siteDescription-{{ $code }}"
                            rows="3"
                            dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-white/10 dark:bg-white/5 dark:text-white"
                        ></textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t border-slate-200 pt-6 dark:border-white/10">
            <x-input-label for="supportEmail" value="Support email" />
            <x-text-input
                wire:model="supportEmail"
                id="supportEmail"
                type="email"
                placeholder="support@example.com"
                :error="$errors->first('supportEmail')"
            />
            <x-input-error :message="$errors->first('supportEmail')" />
        </div>

        <label class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 dark:border-white/10">
            <input wire:model="maintenanceMode" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-white/20 dark:bg-white/5">
            <span>
                <span class="block text-sm font-medium text-slate-900 dark:text-white">Maintenance mode</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">Temporarily mark the site as under maintenance.</span>
            </span>
        </label>

        <x-primary-button wire:loading.attr="disabled" wire:target="save">
            Save settings
        </x-primary-button>
    </form>
</div>
