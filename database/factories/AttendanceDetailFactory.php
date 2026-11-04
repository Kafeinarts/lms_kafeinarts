<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceDetail>
 */
class AttendanceDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'student_id' => User::factory()->murid(),
            'status' => 'H',
            'reason' => null,
        ];
    }
}
