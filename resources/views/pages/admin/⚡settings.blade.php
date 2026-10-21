<?php

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Validation\Rule;
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

    public string $dateFormat = 'M j, Y';

    public array $locales = ['en' => 'English', 'ar' => 'العربية', 'fr' => 'Français'];

    protected function rules(): array
    {
        return [
            'dateFormat' => ['required', Rule::in(Setting::DATE_FORMATS)],
        ];
    }

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
        $this->dateFormat = $setting->date_format;
    }

    public function save(SettingService $settings): void
    {
        $this->authorize('manage settings');

        $this->validate();

        $setting = $settings->current();

        foreach (array_keys($this->locales) as $locale) {
            $setting->setTranslation('site_name', $locale, $this->siteName[$locale] ?? '');
            $setting->setTranslation('site_description', $locale, $this->siteDescription[$locale] ?? '');
        }

        $settings->update($setting, [
            'support_email' => $this->supportEmail ?: null,
            'maintenance_mode' => $this->maintenanceMode,
            'date_format' => $this->dateFormat,
        ]);

        $this->dispatch('settings-saved');
    }
};
?>

<div>
    <div class="ms-mb-24">
        <h2>{{ __('settings.title') }}</h2>
        <p class="text-muted ms-note">{{ __('settings.description') }}</p>
    </div>

    <div data-show-on="settings-saved" class="tag tag-accent">
        <span class="ms-block-pad">{{ __('settings.saved') }}</span>
    </div>

    <form wire:submit="save" class="card ms-mt16-stack-24">
        <div>
            <div class="ms-tabs">
                @foreach ($locales as $code => $label)
                    <button
                        type="button"
                        wire:click="$set('activeLocale', '{{ $code }}')"
                        @class(['ms-locale-tab', 'active' => $activeLocale === $code])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @foreach ($locales as $code => $label)
                <div @class(['ms-locale-panel', 'active' => $activeLocale === $code])>
                    <div class="field ms-mb-0">
                        <x-input-label :for="'siteName-'.$code" :value="__('settings.site_name', ['locale' => $label])" />
                        <x-text-input
                            wire:model="siteName.{{ $code }}"
                            id="siteName-{{ $code }}"
                            type="text"
                            :dir="$code === 'ar' ? 'rtl' : 'ltr'"
                        />
                    </div>

                    <div class="field ms-mb-0">
                        <x-input-label :for="'siteDescription-'.$code" :value="__('settings.site_description', ['locale' => $label])" />
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

        <div class="field ms-section-top">
            <x-input-label for="supportEmail" :value="__('settings.support_email')" />
            <x-text-input
                wire:model="supportEmail"
                id="supportEmail"
                type="email"
                placeholder="{{ __('settings.support_email_placeholder') }}"
                :error="$errors->first('supportEmail')"
            />
            <x-input-error :message="$errors->first('supportEmail')" />
        </div>

        <div class="field">
            <x-input-label for="dateFormat" :value="__('settings.date_format')" />
            <select wire:model="dateFormat" id="dateFormat" class="input">
                @foreach (Setting::DATE_FORMATS as $format)
                    <option value="{{ $format }}">{{ now()->format($format) }}</option>
                @endforeach
            </select>
            <x-input-error :message="$errors->first('dateFormat')" />
        </div>

        <label class="ms-option-card">
            <input wire:model="maintenanceMode" type="checkbox" class="checkbox ms-mt-2">
            <span>
                <span class="ms-block-strong">{{ __('settings.maintenance_mode') }}</span>
                <span class="text-muted ms-block-fs12">{{ __('settings.maintenance_description') }}</span>
            </span>
        </label>

        <div>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                {{ __('settings.save') }}
            </button>
        </div>
    </form>
</div>
