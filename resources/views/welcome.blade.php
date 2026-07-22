<x-layouts.marketing>
    @push('styles')
        <style>
            .hero-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; padding: 88px 48px; }
            .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: var(--color-divider); border: 1px solid var(--color-divider); }
            .feature-cell { background: var(--color-bg); padding: 28px; }
            .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; text-align: center; }
            @media (max-width: 900px) {
                .hero-grid { grid-template-columns: 1fr; gap: 36px; padding: 56px 24px; }
                .feature-grid { grid-template-columns: 1fr; }
                .stat-grid { grid-template-columns: 1fr; gap: 28px; }
            }
        </style>
    @endpush

    <div class="container hero-grid">
        <div>
            <div class="tag tag-accent ms-mb-20">{{ __('marketing.hero_tag') }}</div>
            <h1 class="ms-mw-480">{{ __('marketing.hero_title') }}</h1>
            <p class="text-muted ms-lead-440">{{ __('marketing.hero_description', ['name' => config('app.name')]) }}</p>
            <div class="ms-cta-row">
                <a href="{{ route('register') }}" class="btn btn-primary blueprint">
                    <i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i>
                    {{ __('marketing.get_started') }}
                </a>
                <a href="https://github.com" class="btn">{{ __('marketing.view_on_github') }}</a>
            </div>
        </div>
        <div class="blueprint ms-p-6">
            <i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i>
            <div class="hatch ms-figure">
                <div class="ms-tint"></div>
                <span class="ms-code-chip">dashboard preview</span>
            </div>
        </div>
    </div>

    <div class="container ms-pb-96" id="features">
        <div class="ms-mw-560-mb44">
            <h2>{{ __('docs.features') }}</h2>
            <p class="text-muted">{{ __('docs.features_body') }}</p>
        </div>

        <div class="feature-grid">
            @foreach ([
                ['Security', __('docs.security'), __('docs.security_body')],
                ['People', __('docs.people'), __('docs.people_body')],
                ['Data', __('docs.data'), __('docs.data_body')],
                ['Trust', __('docs.trust'), __('docs.trust_body')],
                ['Config', __('docs.config'), __('docs.config_body')],
                ['Speed', __('docs.speed'), __('docs.speed_body')],
            ] as [$kicker, $title, $body])
                <div class="feature-cell">
                    <div class="card-kicker">{{ $kicker }}</div>
                    <div class="card-title">{{ $title }}</div>
                    <p class="card-body">{{ $body }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="stat-band">
        <div class="container stat-grid">
            <div><div class="stat-value">&lt;10 min</div><div class="stat-label">from clone to running admin</div></div>
            <div><div class="stat-value">12+</div><div class="stat-label">Livewire modules included</div></div>
            <div><div class="stat-value">Laravel 13</div><div class="stat-label">on the latest framework</div></div>
        </div>
    </div>
</x-layouts.marketing>
