<?php

namespace Tests\Unit\Models;

use App\Enums\TaskStages;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task Model Test
 *
 * Unit tests for the Task model
 */
class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a task belongs to a user
     *
     * @return void
     */
    public function test_task_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    /**
     * Test that tasks can be created with different stages
     *
     * @return void
     */
    public function test_task_can_have_different_stages(): void
    {
        $backlogTask = Task::factory()->backlog()->create();
        $inProgressTask = Task::factory()->inProgress()->create();
        $reviewTask = Task::factory()->review()->create();
        $doneTask = Task::factory()->done()->create();

        $this->assertEquals(TaskStages::BACKLOG->value, $backlogTask->stage);
        $this->assertEquals(TaskStages::IN_PROGRESS->value, $inProgressTask->stage);
        $this->assertEquals(TaskStages::REVIEW->value, $reviewTask->stage);
        $this->assertEquals(TaskStages::DONE->value, $doneTask->stage);
    }

    /**
     * Test that task has the correct fillable attributes
     *
     * @return void
     */
    public function test_task_has_correct_fillable_attributes(): void
    {
        $task = new Task;

        $expectedFillable = [
            'title',
            'description',
            'stage',
            'index',
            'user_id',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($expectedFillable, $task->getFillable());
    }
}
