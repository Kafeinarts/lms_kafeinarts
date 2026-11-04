<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\GradeComponent;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeComponent>
 */
class GradeComponentFactory extends Factory
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
            'mapel_id' => Mapel::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'component' => $this->faker->randomElement(['uts', 'uas']),
            'score' => $this->faker->numberBetween(50, 100),
        ];
    }
}
