<?php

namespace Database\Seeders;

use App\Enums\TaskStages;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Task Seeder
 *
 * Creates 20-100 tasks per stage for each user
 */
class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        // Get all stages
        $stages = TaskStages::cases();

        // For each user, create 20-100 tasks per stage
        foreach ($users as $user) {
            foreach ($stages as $stage) {
                // Random number between 20 and 100
                $taskCount = rand(20, 100);

                // Create tasks for this user and stage
                Task::factory($taskCount)
                    ->withStage($stage->value)
                    ->create([
                        'user_id' => $user->id,
                    ]);
            }
        }
    }
}
