<x-layouts.marketing :title="config('app.name').' — Documentation'">
    <div class="container page-head">
        <div class="tag tag-accent ms-mb-20">{{ __('docs.title') }}</div>
        <h1 class="ms-mw-560">{{ __('docs.hero_title', ['name' => config('app.name')]) }}</h1>
        <p class="text-muted ms-lead">{{ __('docs.hero_description') }}</p>
    </div>

    <div class="container ms-pb-96">
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
