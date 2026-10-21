<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class QueuedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_notifications_are_queued(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new QueuedResetPassword('token'));
        $this->assertInstanceOf(ShouldQueue::class, new QueuedVerifyEmail);
    }

    public function test_password_reset_mail_is_dispatched_through_the_queued_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $user->sendPasswordResetNotification('token');

        Notification::assertSentTo($user, QueuedResetPassword::class);
    }
}
