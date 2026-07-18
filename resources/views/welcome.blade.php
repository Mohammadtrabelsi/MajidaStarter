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
            <div class="tag tag-accent" style="margin-bottom: 20px;">Laravel + Livewire Starter Kit</div>
            <h1 style="max-width: 480px;">Admin &amp; auth scaffolding, done right.</h1>
            <p class="text-muted" style="font-size: 16px; max-width: 440px;">{{ config('app.name') }} ships a full Livewire admin — users, roles, activity logs, settings — and a marketing front end, so you skip the boilerplate and start on the product.</p>
            <div style="display: flex; gap: 12px; margin: 28px 0 32px; flex-wrap: wrap;">
                <a href="{{ route('register') }}" class="btn btn-primary blueprint">
                    <i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i>
                    Get Started
                </a>
                <a href="https://github.com" class="btn">View on GitHub</a>
            </div>
        </div>
        <div class="blueprint" style="padding: 6px;">
            <i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i>
            <div class="hatch" style="position: relative; overflow: hidden; aspect-ratio: 4 / 3; display: flex; align-items: center; justify-content: center;">
                <div style="position: absolute; inset: 0; background: var(--color-accent); mix-blend-mode: color;"></div>
                <span style="font-family: monospace; font-size: 12px; background: var(--color-bg); padding: 4px 10px; border: 1px solid var(--color-divider);">dashboard preview</span>
            </div>
        </div>
    </div>

    <div class="container" id="features" style="padding-bottom: 96px;">
        <div style="max-width: 560px; margin-bottom: 44px;">
            <h2>Everything your app needs on day one</h2>
            <p class="text-muted">A Livewire-native admin panel and a matching front end — wired together, ready to extend.</p>
        </div>

        <div class="feature-grid">
            @foreach ([
                ['Security', 'Auth & roles', 'Breeze-style auth plus a full roles & permissions layer, gated at the route and component level.'],
                ['People', 'User management', 'Search, filter and manage every account, with admin promotion built in.'],
                ['Data', 'CRUD scaffolding', 'Livewire tables with search, filters and pagination generated straight from your models.'],
                ['Trust', 'Activity log', 'Every write is recorded — who, what, when — searchable from the admin.'],
                ['Config', 'Settings panel', 'Translatable site settings, support contact and maintenance mode, ready to wire up.'],
                ['Speed', 'Ready to ship', 'A tested foundation so you can start on the product, not the plumbing.'],
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
