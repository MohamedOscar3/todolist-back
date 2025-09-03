<?php

namespace Tests\Unit\Dtos\Task;

use App\Dtos\Task\TaskDto;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task DTO Test
 *
 * Unit tests for the TaskDto class
 */
class TaskDtoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a TaskDto can be created from a Task model
     *
     * @return void
     */
    public function test_can_create_from_model(): void
    {
        // Create a task
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'stage' => 'backlog',
        ]);

        // Create DTO from model
        $dto = TaskDto::fromModel($task);

        // Assert DTO properties match model properties
        $this->assertEquals($task->id, $dto->id);
        $this->assertEquals($task->title, $dto->title);
        $this->assertEquals($task->description, $dto->description);
        $this->assertEquals($task->stage, $dto->stage);
        $this->assertEquals($task->user_id, $dto->user_id);
        $this->assertEquals($task->created_at->toIso8601String(), $dto->created_at);
        $this->assertEquals($task->updated_at->toIso8601String(), $dto->updated_at);
    }

    /**
     * Test that a TaskDto can be converted to an array
     *
     * @return void
     */
    public function test_can_convert_to_array(): void
    {
        // Create a task
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create DTO from model
        $dto = TaskDto::fromModel($task);

        // Convert DTO to array
        $array = $dto->toArray();

        // Assert array contains expected keys and values
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('stage', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);

        $this->assertEquals($task->id, $array['id']);
        $this->assertEquals($task->title, $array['title']);
        $this->assertEquals($task->description, $array['description']);
        $this->assertEquals($task->stage, $array['stage']);
        $this->assertEquals($task->user_id, $array['user_id']);
        $this->assertEquals($task->created_at->toIso8601String(), $array['created_at']);
        $this->assertEquals($task->updated_at->toIso8601String(), $array['updated_at']);
    }
}
