<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;

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
    | notifications table and is listed on the admin notifications page. Add
    | 'broadcast' once Reverb/Pusher is configured to push the same payload to
    | Laravel Echo in real time.
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
    | An update that only touches these attributes does not notify. Laravel
    | rotates the remember token on every "remember me" login, and timestamps
    | and the audit column change on any save. Restoring a soft-deleted record
    | saves it with deleted_at cleared, which would otherwise notify twice
    | (once as "updated", once as "restored").
    */
    'ignored_attributes' => ['updated_at', 'updated_by', 'remember_token', 'deleted_at'],

    /*
    | Models to watch. Each entry describes how to render the notification
    | payload for that resource:
    |   - label:           translation key (or plain text) for the resource name
    |   - title_attribute: attribute used to name the specific record
    |   - events:          which events fire a notification: created, updated,
    |                      deleted, restored
    |   - routes.view:     named route to the record (created/updated/restored)
    |   - routes.index:    named route used after a record is deleted
    |
    | A route that takes the model as a parameter receives it; a route without
    | parameters (such as the settings page) is linked as-is.
    */
    'models' => [

        Post::class => [
            'label' => 'notifications.resources.post',
            'title_attribute' => 'title',
            'events' => ['created', 'updated', 'deleted', 'restored'],
            'routes' => [
                'view' => 'admin.posts.edit',
                'index' => 'admin.posts.index',
            ],
        ],

        Category::class => [
            'label' => 'notifications.resources.category',
            'title_attribute' => 'name',
            'events' => ['created', 'updated', 'deleted', 'restored'],
            'routes' => [
                'view' => 'admin.categories.edit',
                'index' => 'admin.categories.index',
            ],
        ],

        User::class => [
            'label' => 'notifications.resources.user',
            'title_attribute' => 'name',
            'events' => ['created', 'updated', 'deleted', 'restored'],
            'routes' => [
                'view' => 'admin.users.edit',
                'index' => 'admin.users.index',
            ],
        ],

        Setting::class => [
            'label' => 'notifications.resources.settings',
            'title_attribute' => 'site_name',
            'events' => ['updated'],
            'routes' => [
                'view' => 'admin.settings',
                'index' => 'admin.settings',
            ],
        ],

    ],

];
