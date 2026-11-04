<?php

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->murid(),
            'recorded_by' => User::factory()->guru(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'points' => $this->faker->numberBetween(5, 50),
            'achieved_at' => now()->toDateString(),
        ];
    }
}
