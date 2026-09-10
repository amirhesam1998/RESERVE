<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'arian',
            'last_name' => 'mollaie',
            'email' => 'arian@gmail.com',
            'password' => '123456789a',
            'level' => 'creator',
            'phone_number' => '123-456-7890'
        ]);

        $this->call([
            PermissionSeeder::class,
            CartSeeder::class
        ]);
    }
}
