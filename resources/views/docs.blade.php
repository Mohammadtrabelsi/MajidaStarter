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
        <div class="tag tag-accent" style="margin-bottom: 20px;">{{ __('docs.title') }}</div>
        <h1 style="max-width: 560px;">{{ __('docs.hero_title') }}</h1>
        <p class="text-muted" style="font-size: 16px; max-width: 520px;">{{ __('docs.hero_description') }}</p>
    </div>

    <div class="container" style="padding-bottom: 96px;">
        <div class="docs-grid">
            @foreach ([
                ['Getting started', __('docs.getting_started_body'), __('docs.readme'), 'https://github.com'],
                ['Authentication', __('docs.authentication_body'), __('docs.auth_routes'), url('/login')],
                ['Roles & permissions', __('docs.roles_permissions_body'), __('docs.admin_dashboard'), url('/dashboard')],
                ['User management', __('docs.user_management_body'), __('docs.users'), url('/dashboard')],
                ['CRUD scaffolding', __('docs.crud_scaffolding_body'), __('docs.posts_categories'), url('/dashboard')],
                ['Settings & activity log', __('docs.settings_activity_log_body'), __('docs.settings'), url('/dashboard')],
            ] as [$title, $body, $linkLabel, $linkUrl])
                <div class="docs-cell">
                    <div class="card-kicker">{{ __('docs.guide') }}</div>
                    <h3>{{ $title }}</h3>
                    <p>{{ $body }}</p>
                    <a href="{{ $linkUrl }}">{{ $linkLabel }} →</a>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.marketing>
