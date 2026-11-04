<?php

namespace Database\Factories;

use App\Models\DiscussionTopic;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscussionTopic>
 */
class DiscussionTopicFactory extends Factory
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
            'user_id' => User::factory()->murid(),
            'title' => $this->faker->sentence(5),
            'body' => $this->faker->paragraph(),
        ];
    }
}
