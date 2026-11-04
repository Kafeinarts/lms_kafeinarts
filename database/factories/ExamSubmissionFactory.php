<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamSubmission>
 */
class ExamSubmissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ExamSubmission>
     */
    protected $model = ExamSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'user_id' => User::factory()->murid(),
            'answers' => [],
            'score' => fake()->numberBetween(0, 100),
            'submitted_at' => now(),
        ];
    }
}
