<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=Server>
 */
class ServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->username(),
            'username' => fake()->username(),
            'address' => fake()->ipv4(),
            'port' => random_int(22, 6000),
            'udp_port' => random_int(1000, 6000)
        ];
    }
}
