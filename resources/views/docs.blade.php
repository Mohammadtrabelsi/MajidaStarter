<x-layouts.marketing :title="config('app.name').' — Documentation'">
    @push('styles')
        <style>
            .page-head { padding: 72px 48px 40px; }
            .docs-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1px; background: var(--color-divider); border: 1px solid var(--color-divider); }
            .docs-cell { background: var(--color-bg); padding: 28px; }
            .docs-cell h3 { font-family: var(--font-heading); font-weight: 600; font-size: 18px; margin: 0 0 8px; }
            .docs-cell p { font-size: 13px; opacity: 0.75; margin: 0 0 14px; }
            .docs-cell a { font-size: 13px; color: var(--color-accent); text-decoration: none; }
            .docs-cell a:hover { text-decoration: underline; }
            @media (max-width: 900px) {
                .page-head { padding: 48px 24px 32px; }
                .docs-grid { grid-template-columns: 1fr; }
            }
        </style>
    @endpush

    <div class="container page-head">
        <div class="tag tag-accent" style="margin-bottom: 20px;">Documentation</div>
        <h1 style="max-width: 560px;">Get up and running with {{ config('app.name') }}</h1>
        <p class="text-muted" style="font-size: 16px; max-width: 520px;">Everything you need to install the starter kit, understand the admin panel, and extend it for your own product.</p>
    </div>

    <div class="container" style="padding-bottom: 96px;">
        <div class="docs-grid">
            @foreach ([
                ['Getting started', 'Clone the repository, install dependencies, run the migrations and seeders, and launch the app in under ten minutes.', 'README', 'https://github.com'],
                ['Authentication', 'Breeze-style login, registration, email verification and password resets — all built on Livewire components.', 'Auth routes', url('/login')],
                ['Roles & permissions', 'How the roles and permissions layer gates routes and Livewire components, and how to add your own abilities.', 'Admin dashboard', url('/dashboard')],
                ['User management', 'Search, filter, create and edit accounts, and promote users to admin from the management screen.', 'Users', url('/dashboard')],
                ['CRUD scaffolding', 'Generate Livewire tables with search, filters and pagination straight from your Eloquent models.', 'Posts & categories', url('/dashboard')],
                ['Settings & activity log', 'Configure translatable site settings and maintenance mode, and audit every write in the activity log.', 'Settings', url('/dashboard')],
            ] as [$title, $body, $linkLabel, $linkUrl])
                <div class="docs-cell">
                    <div class="card-kicker">Guide</div>
                    <h3>{{ $title }}</h3>
                    <p>{{ $body }}</p>
                    <a href="{{ $linkUrl }}">{{ $linkLabel }} →</a>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.marketing>
