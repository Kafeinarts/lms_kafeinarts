<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\FinalGrade;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinalGrade>
 */
class FinalGradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $score = $this->faker->numberBetween(55, 100);
        $letter = match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };

        return [
            'student_id' => User::factory()->murid(),
            'mapel_id' => Mapel::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'final_score' => $score,
            'grade_letter' => $letter,
            'teacher_notes' => null,
        ];
    }
}
