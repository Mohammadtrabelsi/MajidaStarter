<x-layouts.marketing :title="config('app.name').' — '.__('maintenance.title')">
    @push('styles')
        <style>
            .maintenance { padding: 120px 48px; text-align: center; }
            .maintenance h1 { font-family: var(--font-heading); font-weight: 600; font-size: 36px; margin: 0 0 16px; }
            @media (max-width: 900px) {
                .maintenance { padding: 80px 24px; }
            }
        </style>
    @endpush

    <div class="container maintenance">
        <div class="tag tag-accent ms-mb-20">{{ __('maintenance.badge') }}</div>
        <h1>{{ __('maintenance.title') }}</h1>
        <p class="text-muted ms-lead">{{ __('maintenance.body') }}</p>
    </div>
</x-layouts.marketing>
