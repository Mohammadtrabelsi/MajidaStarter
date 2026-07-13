@php($locales = config('app.available_locales', []))

@if (count($locales) > 1)
    <div style="position: relative;" x-data="{ open: false }">
        <button type="button" @click="open = !open" @click.outside="open = false" class="icon-btn" title="Change language">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"></circle><path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18"></path></svg>
            <span style="text-transform: uppercase; font-size: 12px;">{{ app()->getLocale() }}</span>
        </button>

        <div x-show="open" x-transition style="display: none;" class="menu-pop">
            @foreach ($locales as $code => $meta)
                <a
                    href="{{ route('locale.switch', $code) }}"
                    class="{{ app()->getLocale() === $code ? 'active' : '' }}"
                >
                    {{ $meta['label'] ?? strtoupper($code) }}
                </a>
            @endforeach
        </div>
    </div>
@endif
