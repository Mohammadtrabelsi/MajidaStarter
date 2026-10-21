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
 * config/crud_notifications.php, that turns create/update/delete/restore events
 * into queued database notifications for the configured recipients.
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
        if ($this->onlyIgnoredAttributesChanged($model)) {
            return;
        }

        $this->dispatch($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->dispatch($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        $this->dispatch($model, 'restored');
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
     * True when the save changed nothing but the attributes listed in
     * config('crud_notifications.ignored_attributes').
     */
    private function onlyIgnoredAttributesChanged(Model $model): bool
    {
        $ignored = (array) config('crud_notifications.ignored_attributes', []);

        return array_diff(array_keys($model->getChanges()), $ignored) === [];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function payload(Model $model, string $event, array $config, ?int $actorId): array
    {
        $label = __($config['label'] ?? class_basename($model));
        $name = $this->resourceName($model, $config, $label);

        return [
            'title' => __("notifications.titles.{$event}", ['label' => $label]),
            'message' => __("notifications.messages.{$event}", ['label' => $label, 'name' => $name]),
            'action_url' => $this->actionUrl($model, $event, $config),
            'type' => match ($event) {
                'created', 'restored' => 'success',
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
    private function resourceName(Model $model, array $config, string $label): string
    {
        $attribute = $config['title_attribute'] ?? null;
        $value = $attribute ? $model->getAttribute($attribute) : null;

        if (is_array($value)) {
            $value = $value[app()->getLocale()] ?? (reset($value) ?: null);
        }

        if (is_scalar($value) && (string) $value !== '') {
            return (string) $value;
        }

        return $label.' #'.$model->getKey();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function actionUrl(Model $model, string $event, array $config): ?string
    {
        $routes = $config['routes'] ?? [];

        if ($event === 'deleted') {
            return $this->routeUrl($routes['index'] ?? null);
        }

        return $this->routeUrl($routes['view'] ?? null, $model);
    }

    /**
     * Links the named route, passing the model only when the route declares a
     * parameter for it. Passing it to a parameterless route would append it to
     * the query string.
     */
    private function routeUrl(?string $name, ?Model $model = null): ?string
    {
        if ($name === null || ! Route::has($name)) {
            return null;
        }

        $takesModel = Route::getRoutes()->getByName($name)?->parameterNames() !== [];

        return $model !== null && $takesModel ? route($name, [$model]) : route($name);
    }
}
