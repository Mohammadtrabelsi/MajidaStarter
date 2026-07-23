<x-layouts.marketing :title="config('app.name').' — Blog'">
    <div class="container page-head">
        <div class="tag tag-accent ms-mb-20">{{ __('blog.title') }}</div>
        <h1 class="ms-mw-560">News &amp; notes from {{ config('app.name') }}</h1>
        <p class="text-muted ms-lead">{{ __('blog.description') }}</p>
    </div>

    <div class="container ms-pb-96">
        <div class="post-list">
            @foreach ([
                ['Jul 2026', __('blog.post_1_title'), __('blog.post_1_excerpt')],
                ['Jun 2026', __('blog.post_2_title'), __('blog.post_2_excerpt')],
                ['Jun 2026', __('blog.post_3_title'), __('blog.post_3_excerpt')],
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
