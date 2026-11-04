<?php

namespace Database\Factories;

use App\Models\CounselingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CounselingSession>
 */
class CounselingSessionFactory extends Factory
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
            'counselor_id' => User::factory()->guru(),
            'session_date' => now()->toDateString(),
            'topic' => $this->faker->sentence(4),
            'notes' => $this->faker->paragraph(),
            'follow_up' => $this->faker->sentence(),
        ];
    }
}
