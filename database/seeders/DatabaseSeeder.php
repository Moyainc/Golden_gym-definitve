<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Solo ejecuta el seeder de super admin
        $this->call([
            SuperAdminSeeder::class,
        ]);
    }
}
