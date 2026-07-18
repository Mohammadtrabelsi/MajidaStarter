<x-layouts.marketing :title="config('app.name').' — Blog'">
    @push('styles')
        <style>
            .page-head { padding: 72px 48px 40px; }
            .post-list { display: flex; flex-direction: column; border-top: 1px solid var(--color-divider); }
            .post-row { display: grid; grid-template-columns: 160px 1fr; gap: 32px; padding: 28px 0; border-bottom: 1px solid var(--color-divider); }
            .post-row .post-date { font-size: 12px; letter-spacing: 0.06em; text-transform: uppercase; color: rgba(var(--ink), 0.55); }
            .post-row h3 { font-family: var(--font-heading); font-weight: 600; font-size: 20px; margin: 0 0 8px; }
            .post-row p { font-size: 14px; opacity: 0.75; margin: 0; max-width: 620px; }
            @media (max-width: 900px) {
                .page-head { padding: 48px 24px 32px; }
                .post-row { grid-template-columns: 1fr; gap: 8px; }
            }
        </style>
    @endpush

    <div class="container page-head">
        <div class="tag tag-accent" style="margin-bottom: 20px;">Blog</div>
        <h1 style="max-width: 560px;">News &amp; notes from {{ config('app.name') }}</h1>
        <p class="text-muted" style="font-size: 16px; max-width: 520px;">Release notes, build logs and lessons from shipping an admin-heavy Laravel starter. New posts land here as the project grows.</p>
    </div>

    <div class="container" style="padding-bottom: 96px;">
        <div class="post-list">
            @foreach ([
                ['Jul 2026', 'Introducing the Majida starter kit', 'A first look at what ships out of the box — Livewire admin, auth, roles, activity log and a matching marketing front end.'],
                ['Jun 2026', 'Why we built the admin on Livewire', 'How full-page Livewire components keep the admin fast to build and easy to extend without a separate SPA.'],
                ['Jun 2026', 'Roles & permissions, the pragmatic way', 'A walkthrough of the gating layer and how to add your own abilities at the route and component level.'],
            ] as [$date, $title, $excerpt])
                <article class="post-row">
                    <div class="post-date">{{ $date }}</div>
                    <div>
                        <h3>{{ $title }}</h3>
                        <p>{{ $excerpt }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</x-layouts.marketing>
