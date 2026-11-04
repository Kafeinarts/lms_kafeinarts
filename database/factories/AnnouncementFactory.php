<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->guru(),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'audience' => $this->faker->randomElement(['all', 'guru', 'murid']),
            'published_at' => now(),
        ];
    }
}
