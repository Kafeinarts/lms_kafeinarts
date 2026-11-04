<?php

namespace Database\Factories;

use App\Models\DiscussionReply;
use App\Models\DiscussionTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscussionReply>
 */
class DiscussionReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_id' => DiscussionTopic::factory(),
            'user_id' => User::factory()->murid(),
            'body' => $this->faker->paragraph(),
        ];
    }
}
