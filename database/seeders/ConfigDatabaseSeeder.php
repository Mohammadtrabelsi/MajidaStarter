<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigDatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed only configuration data (admin user, roles and permissions) without
     * any demo/sample content.
     *
     * Run with: php artisan db:seed --class=ConfigDatabaseSeeder
     */
    public function run(): void
    {
        $this->call([
            ...DatabaseSeeder::MINIMAL,
            ...DatabaseSeeder::REFERENCE,
        ]);
    }
}
