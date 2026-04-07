<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Chelsea', 'Arsenal', 'Liverpool', 'Manchester City']),
            'power' => fake()->numberBetween(70, 95),
            'home_advantage' => fake()->randomFloat(2, 1.05, 1.25),
            'goalkeeper_factor' => fake()->randomFloat(2, 0.6, 0.95),
        ];
    }
}
