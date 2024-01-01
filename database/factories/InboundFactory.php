<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=Inbound>
 */
class InboundFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $traffic_limit = random_int(50, 100);
        return [
            'username' => fake()->userName(),
            'password' => 'password', //static::$password ??= Hash::make('password'),
            'traffic_limit' => $traffic_limit,
            'remaining_traffic' => $traffic_limit - random_int(1, 49),
            'max_login' => random_int(1, 5),
            'is_active' => strval(random_int(0, 1)),
            'server_ip' => '1.1.1.1',
            'expires_at' => Carbon::now()->addDays(random_int(10, 100)),
        ];
    }
}
