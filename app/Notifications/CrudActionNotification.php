<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Generic, queued CRUD notification. The full presentation payload is built by
 * the observer and passed in, so this single class serves every resource
 * instead of one notification class per model.
 *
 * @phpstan-type CrudPayload array{
 *     title: string,
 *     message: string,
 *     action_url: string|null,
 *     type: string,
 *     icon: string,
 *     event: string,
 *     model_type: string,
 *     model_id: int|string|null,
 *     sender_id: int|null
 * }
 */
class CrudActionNotification extends Notification implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * @param  CrudPayload  $payload
     * @param  list<string>  $channels
     */
    public function __construct(
        public array $payload,
        public array $channels = ['database'],
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * @return CrudPayload
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload);
    }
}
