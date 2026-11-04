<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\GradeWeight;
use App\Models\Mapel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeWeight>
 */
class GradeWeightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mapel_id' => Mapel::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'assignment_weight' => 30,
            'uts_weight' => 30,
            'uas_weight' => 40,
        ];
    }
}
