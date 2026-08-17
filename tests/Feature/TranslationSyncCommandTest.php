<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TranslationSyncCommandTest extends TestCase
{
    private string $langPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Redirect the lang directory to a throwaway location so the command
        // writes there instead of mutating the repository's real lang files.
        // Scanning still targets the real resources/ directory (read-only).
        $this->langPath = sys_get_temp_dir().'/transync_'.uniqid();
        File::ensureDirectoryExists($this->langPath);
        app()->useLangPath($this->langPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->langPath);

        parent::tearDown();
    }

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

    public function test_dry_run_does_not_write_any_files(): void
    {
        $this->artisan('translations:sync', ['--dry-run' => true, '--locales' => 'en'])
            ->assertSuccessful();

        $this->assertDirectoryDoesNotExist($this->langPath.'/en');
    }

    public function test_run_creates_locale_directories_and_key_files(): void
    {
        $this->artisan('translations:sync', ['--locales' => 'en'])
            ->assertSuccessful();

        $this->assertDirectoryExists($this->langPath.'/en');
        $this->assertNotEmpty(File::files($this->langPath.'/en'));
    }

    public function test_non_base_locale_receives_todo_placeholders(): void
    {
        $this->artisan('translations:sync', ['--locales' => 'en,ar', '--base' => 'en'])
            ->assertSuccessful();

        $this->assertDirectoryExists($this->langPath.'/ar');

        $arContents = collect(File::files($this->langPath.'/ar'))
            ->map(fn ($file) => File::get($file->getPathname()))
            ->implode("\n");

        $this->assertStringContainsString('[TODO]', $arContents);
    }

    public function test_base_locale_values_are_not_marked_todo(): void
    {
        $this->artisan('translations:sync', ['--locales' => 'en', '--base' => 'en'])
            ->assertSuccessful();

        $enContents = collect(File::files($this->langPath.'/en'))
            ->map(fn ($file) => File::get($file->getPathname()))
            ->implode("\n");

        $this->assertStringNotContainsString('[TODO]', $enContents);
    }

    public function test_clean_option_removes_stale_keys_and_adds_scanned_ones(): void
    {
        File::ensureDirectoryExists($this->langPath.'/en');
        $marketingFile = $this->langPath.'/en/marketing.php';
        File::put($marketingFile, "<?php\n\nreturn ['zzz_stale_key' => 'remove me'];\n");

        $this->artisan('translations:sync', ['--locales' => 'en', '--base' => 'en', '--clean' => true])
            ->assertSuccessful();

        $result = include $marketingFile;

        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('zzz_stale_key', $result);
        // The scan should have added real marketing.* keys used across the views.
        $this->assertNotEmpty($result);
    }

    public function test_second_run_is_idempotent(): void
    {
        $this->artisan('translations:sync', ['--locales' => 'en'])->assertSuccessful();

        // Re-running against already-synced files must still succeed and
        // report the files as up to date.
        $this->artisan('translations:sync', ['--locales' => 'en'])
            ->expectsOutputToContain('Translation files are up to date.')
            ->assertSuccessful();
    }
}
