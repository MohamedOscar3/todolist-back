<?php

namespace Database\Factories;

use App\Enums\TaskStages;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Task Factory
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Task>
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stages = array_column(TaskStages::cases(), 'value');

        return [
            'title' => $this->faker->sentence(rand(3, 8)),
            'description' => $this->faker->paragraphs(rand(1, 3), true),
            'stage' => $this->faker->randomElement($stages),
            'index' => rand(0, 10), // Add random index for testing
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Set the task stage to a specific value
     *
     * @param string $stage
     *
     * @return Factory
     */
    public function withStage(string $stage): Factory
    {
        return $this->state(function (array $attributes) use ($stage) {
            return [
                'stage' => $stage,
            ];
        });
    }

    /**
     * Set the task to backlog stage
     *
     * @return Factory
     */
    public function backlog(): Factory
    {
        return $this->withStage(TaskStages::BACKLOG->value);
    }

    /**
     * Set the task to in progress stage
     *
     * @return Factory
     */
    public function inProgress(): Factory
    {
        return $this->withStage(TaskStages::IN_PROGRESS->value);
    }

    /**
     * Set the task to review stage
     *
     * @return Factory
     */
    public function review(): Factory
    {
        return $this->withStage(TaskStages::REVIEW->value);
    }

    /**
     * Set the task to done stage
     *
     * @return Factory
     */
    public function done(): Factory
    {
        return $this->withStage(TaskStages::DONE->value);
    }
}
