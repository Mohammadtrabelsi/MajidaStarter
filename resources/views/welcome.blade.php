<x-layouts.marketing>
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
