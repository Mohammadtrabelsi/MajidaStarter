<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MinimalDatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed only the minimal baseline: admin user, roles and permissions.
     *
     * Run with: php artisan db:seed --class=MinimalDatabaseSeeder
     */
    public function run(): void
    {
        $this->call(DatabaseSeeder::MINIMAL);
    }
}
