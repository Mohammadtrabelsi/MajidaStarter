<?php

namespace App\Support\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Resolves the users who should receive persistent CRUD notifications.
 *
 * Recipients are the users holding any of the configured roles, with the
 * acting user excluded so nobody is notified about their own action.
 */
class CrudNotificationRecipients
{
    /**
     * @return Collection<int, User>
     */
    public function forEvent(?int $excludeUserId = null): Collection
    {
        $roles = array_values(array_filter((array) config('crud_notifications.recipients.roles', [])));

        return User::query()
            ->when($roles !== [], fn ($query) => $query->role($roles))
            ->when($excludeUserId !== null, fn ($query) => $query->whereKeyNot($excludeUserId))
            ->get();
    }
}
