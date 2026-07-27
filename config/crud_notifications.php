<?php

use App\Models\Category;
use App\Models\Post;

return [

    /*
    |--------------------------------------------------------------------------
    | CRUD Notifications
    |--------------------------------------------------------------------------
    |
    | Central configuration for the decoupled CRUD notification system. A single
    | generic observer (App\Observers\CrudNotificationObserver) watches the
    | models listed below and, on the configured events, sends a
    | App\Notifications\CrudActionNotification to the resolved recipients.
    |
    | This keeps notification logic out of Livewire components and models: to
    | notify on a new resource, add it here — no new classes required.
    |
    */

    'enabled' => env('CRUD_NOTIFICATIONS_ENABLED', true),

    /*
    | Channels used by the notification. 'database' persists to the
    | notifications table. Add 'broadcast' once Reverb/Pusher is configured to
    | push the same payload to Laravel Echo in real time.
    */
    'channels' => ['database'],

    /*
    | Who receives the persistent notifications. Recipients are resolved from
    | the roles below (Spatie roles) and the acting user is always excluded so
    | people are never notified about their own actions.
    */
    'recipients' => [
        'roles' => ['admin'],
    ],

    /*
    | Models to watch. Each entry describes how to render the notification
    | payload for that resource:
    |   - label:           human friendly resource name
    |   - title_attribute: attribute used to name the specific record
    |   - events:          which CRUD events fire a notification
    |   - routes.view:     named route to the record (created/updated)
    |   - routes.index:    named route used after a record is deleted
    */
    'models' => [

        Post::class => [
            'label' => 'Post',
            'title_attribute' => 'title',
            'events' => ['created', 'updated', 'deleted'],
            'routes' => [
                'view' => 'admin.posts.edit',
                'index' => 'admin.posts.index',
            ],
        ],

        Category::class => [
            'label' => 'Category',
            'title_attribute' => 'name',
            'events' => ['created', 'updated', 'deleted'],
            'routes' => [
                'view' => 'admin.categories.edit',
                'index' => 'admin.categories.index',
            ],
        ],

    ],

];
