<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\TeachingSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => TeachingSchedule::factory(),
            'teacher_id' => User::factory()->guru(),
            'date' => now()->toDateString(),
            'notes' => null,
        ];
    }
}
