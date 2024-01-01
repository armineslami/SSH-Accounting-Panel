<?php

namespace Database\Seeders;

use App\Models\Inbound;
use App\Models\Server;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'admin',
        ]);

//        Inbound::factory(23)->create();

        Setting::factory(1)->create();

//        Server::factory(1)->create();
    }
}
