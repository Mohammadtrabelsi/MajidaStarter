<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Marketing navbar
    |--------------------------------------------------------------------------
    |
    | Links shown in the public navbar, in display order. Each item needs:
    |
    |   'label' — a translation key (e.g. 'marketing.blog') or literal text
    |   'route' — a named route, or
    |   'url'   — a path or absolute URL (e.g. '/#features')
    |
    | A route item is marked as the current page when its route, or any route
    | nested under it (e.g. 'blog' also matches 'blog.show'), is active.
    |
    */

    'marketing' => [
        ['label' => 'marketing.features', 'url' => '/#features'],
        ['label' => 'marketing.docs', 'route' => 'docs'],
        ['label' => 'marketing.blog', 'route' => 'blog'],
    ],

];
