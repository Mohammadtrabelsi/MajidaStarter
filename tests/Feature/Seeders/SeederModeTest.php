<?php

namespace Tests\Feature\Seeders;

use App\Models\User;
use Database\Seeders\ConfigDatabaseSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MinimalDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_tiers_are_disjoint(): void
    {
        $minimal = DatabaseSeeder::MINIMAL;
        $reference = DatabaseSeeder::REFERENCE;
        $demo = DatabaseSeeder::DEMO;

        $this->assertEmpty(array_intersect($minimal, $reference));
        $this->assertEmpty(array_intersect($minimal, $demo));
        $this->assertEmpty(array_intersect($reference, $demo));
    }

    public function test_full_set_has_no_duplicates(): void
    {
        $all = [...DatabaseSeeder::MINIMAL, ...DatabaseSeeder::REFERENCE, ...DatabaseSeeder::DEMO];

        $this->assertSame($all, array_values(array_unique($all)));
    }

    public function test_minimal_seed_creates_only_admin_user(): void
    {
        $this->seed(MinimalDatabaseSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertTrue(User::first()->hasRole('admin'));
    }

    public function test_config_seed_creates_no_demo_users(): void
    {
        $this->seed(ConfigDatabaseSeeder::class);

        $this->assertSame(1, User::count());
    }

    public function test_full_seed_creates_demo_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(1, User::count());
    }
}
