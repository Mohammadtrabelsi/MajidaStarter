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

<div style="max-width: 720px;">
    <div style="margin-bottom: 24px;">
        <h2>Settings</h2>
        <p class="text-muted" style="font-size: 13px; margin: 0;">Manage general application and localization settings.</p>
    </div>

    <div
        x-data="{ show: false }"
        x-on:settings-saved.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        style="display: none;"
        class="tag tag-accent"
    >
        <span style="display: block; padding: 8px 12px;">Settings saved successfully.</span>
    </div>

    <form wire:submit="save" class="card" style="margin-top: 16px; display: flex; flex-direction: column; gap: 24px;">
        <div>
            <div style="display: flex; gap: 2px; border-bottom: 1px solid var(--color-divider); margin-bottom: 18px;">
                @foreach ($locales as $code => $label)
                    <button
                        type="button"
                        wire:click="$set('activeLocale', '{{ $code }}')"
                        style="padding: 8px 14px; font-size: 13px; font-weight: 500; background: transparent; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-family: inherit; {{ $activeLocale === $code ? 'color: var(--color-accent-800); border-bottom-color: var(--color-accent);' : 'color: rgba(var(--ink),.6);' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @foreach ($locales as $code => $label)
                <div style="flex-direction: column; gap: 16px; display: {{ $activeLocale === $code ? 'flex' : 'none' }};">
                    <div class="field" style="margin-bottom: 0;">
                        <x-input-label :for="'siteName-'.$code" :value="'Site name ('.$label.')'" />
                        <x-text-input
                            wire:model="siteName.{{ $code }}"
                            id="siteName-{{ $code }}"
                            type="text"
                            :dir="$code === 'ar' ? 'rtl' : 'ltr'"
                        />
                    </div>

                    <div class="field" style="margin-bottom: 0;">
                        <x-input-label :for="'siteDescription-'.$code" :value="'Site description ('.$label.')'" />
                        <textarea
                            wire:model="siteDescription.{{ $code }}"
                            id="siteDescription-{{ $code }}"
                            rows="3"
                            dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                            class="textarea"
                        ></textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="field" style="margin-bottom: 0; border-top: 1px solid var(--color-divider); padding-top: 20px;">
            <x-input-label for="supportEmail" value="Support email" />
            <x-text-input
                wire:model="supportEmail"
                id="supportEmail"
                type="email"
                placeholder="support@company.com"
                :error="$errors->first('supportEmail')"
            />
            <x-input-error :message="$errors->first('supportEmail')" />
        </div>

        <label style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid var(--color-divider); padding: 14px; cursor: pointer;">
            <input wire:model="maintenanceMode" type="checkbox" class="checkbox" style="margin-top: 2px;">
            <span>
                <span style="display: block; font-size: 14px; font-weight: 500;">Maintenance mode</span>
                <span class="text-muted" style="display: block; font-size: 12px;">Temporarily mark the site as under maintenance.</span>
            </span>
        </label>

        <div>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                Save settings
            </button>
        </div>
    </form>
</div>
