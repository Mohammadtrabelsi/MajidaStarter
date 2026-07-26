<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TranslationSyncCommandTest extends TestCase
{
    public function test_translations_sync_command_is_registered(): void
    {
        // Regression guard: the command lives in App\Console\Commands and must
        // be discoverable (the directory was previously mis-cased as
        // app/console, which broke PSR-4 autoloading on Linux).
        $this->assertArrayHasKey('translations:sync', Artisan::all());
    }

    public function test_translations_sync_dry_run_succeeds(): void
    {
        $this->artisan('translations:sync', ['--dry-run' => true])
            ->assertSuccessful();
    }
}
