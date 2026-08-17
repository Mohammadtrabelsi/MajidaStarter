<?php

namespace App\Observers;

use App\Notifications\CrudActionNotification;
use App\Support\Notifications\CrudNotificationRecipients;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

/**
 * A single observer, registered against every model listed in
 * config/crud_notifications.php, that turns create/update/delete events into
 * queued database notifications for the configured recipients.
 *
 * Keeping this logic here — rather than in Livewire components, services, or
 * the models themselves — is what decouples the notification concern from the
 * CRUD actions that trigger it.
 */
class CrudNotificationObserver
{
    public function __construct(private CrudNotificationRecipients $recipients) {}

    public function created(Model $model): void
    {
        $this->dispatch($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->dispatch($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->dispatch($model, 'deleted');
    }

    private function dispatch(Model $model, string $event): void
    {
        if (! config('crud_notifications.enabled', true)) {
            return;
        }

        /** @var array<string, mixed>|null $config */
        $config = config('crud_notifications.models.'.$model::class);

        if ($config === null || ! in_array($event, $config['events'] ?? [], true)) {
            return;
        }

        $actorId = Auth::id();
        $recipients = $this->recipients->forEvent($actorId);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new CrudActionNotification(
                $this->payload($model, $event, $config, $actorId),
                array_values((array) config('crud_notifications.channels', ['database'])),
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function payload(Model $model, string $event, array $config, ?int $actorId): array
    {
        $label = $config['label'] ?? class_basename($model);
        $name = $this->resourceName($model, $config);

        return [
            'title' => "{$label} {$event}",
            'message' => "{$label} \"{$name}\" was {$event}.",
            'action_url' => $this->actionUrl($model, $event, $config),
            'type' => match ($event) {
                'created' => 'success',
                'deleted' => 'danger',
                default => 'info',
            },
            'icon' => $event,
            'event' => $event,
            'model_type' => $model::class,
            'model_id' => $model->getKey(),
            'sender_id' => $actorId,
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function resourceName(Model $model, array $config): string
    {
        $attribute = $config['title_attribute'] ?? null;
        $value = $attribute ? $model->getAttribute($attribute) : null;

        if (is_array($value)) {
            $value = $value[app()->getLocale()] ?? (reset($value) ?: null);
        }

        if (is_scalar($value) && (string) $value !== '') {
            return (string) $value;
        }

        return ($config['label'] ?? class_basename($model)).' #'.$model->getKey();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function actionUrl(Model $model, string $event, array $config): ?string
    {
        $routes = $config['routes'] ?? [];

        if ($event === 'deleted') {
            $name = $routes['index'] ?? null;

            return $name && Route::has($name) ? route($name) : null;
        }

        $name = $routes['view'] ?? null;

        return $name && Route::has($name) ? route($name, $model) : null;
    }
}
