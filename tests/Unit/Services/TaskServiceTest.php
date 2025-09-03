<?php

namespace Tests\Unit\Services;

use App\Dtos\Task\CreateTaskDto;
use App\Dtos\Task\UpdateTaskDto;
use App\Enums\TaskStages;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task Service Test
 *
 * Unit tests for the TaskService class
 */
class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The task service instance
     *
     * @var TaskService
     */
    protected TaskService $taskService;

    /**
     * The authenticated user
     *
     * @var User
     */
    protected User $user;

    /**
     * Setup the test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create the task service
        $this->taskService = new TaskService;
    }

    /**
     * Test that tasks can be retrieved grouped by stages
     *
     * @return void
     */
    public function test_can_get_tasks_grouped_by_stages(): void
    {
        // Create tasks for each stage
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
        ]);

        Task::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::IN_PROGRESS->value,
        ]);

        // Create tasks for another user (should not be included)
        $anotherUser = User::factory()->create();
        Task::factory()->count(5)->create([
            'user_id' => $anotherUser->id,
        ]);

        // Get tasks grouped by stages
        $result = $this->taskService->getTasksGroupedByStages();

        // Assert the result contains all stages
        foreach (TaskStages::cases() as $stage) {
            $this->assertArrayHasKey($stage->value, $result);
            $this->assertArrayHasKey('tasks', $result[$stage->value]);
            $this->assertArrayHasKey('meta', $result[$stage->value]);
        }

        // Assert the correct number of tasks for each stage
        $this->assertCount(3, $result[TaskStages::BACKLOG->value]['tasks']);
        $this->assertCount(2, $result[TaskStages::IN_PROGRESS->value]['tasks']);
        $this->assertCount(0, $result[TaskStages::REVIEW->value]['tasks']);
        $this->assertCount(0, $result[TaskStages::DONE->value]['tasks']);
    }

    /**
     * Test that tasks can be filtered by stage
     *
     * @return void
     */
    public function test_can_filter_tasks_by_stage(): void
    {
        // Create tasks for each stage
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
        ]);

        Task::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::IN_PROGRESS->value,
        ]);

        // Get tasks filtered by backlog stage
        $result = $this->taskService->getTasksGroupedByStages(10, TaskStages::BACKLOG->value);

        // Assert the result contains only the backlog stage
        $this->assertCount(1, $result);
        $this->assertArrayHasKey(TaskStages::BACKLOG->value, $result);
        $this->assertArrayNotHasKey(TaskStages::IN_PROGRESS->value, $result);
        $this->assertCount(3, $result[TaskStages::BACKLOG->value]['tasks']);

        // Get tasks filtered by in_progress stage
        $result = $this->taskService->getTasksGroupedByStages(10, TaskStages::IN_PROGRESS->value);

        // Assert the result contains only the in_progress stage
        $this->assertCount(1, $result);
        $this->assertArrayHasKey(TaskStages::IN_PROGRESS->value, $result);
        $this->assertArrayNotHasKey(TaskStages::BACKLOG->value, $result);
        $this->assertCount(2, $result[TaskStages::IN_PROGRESS->value]['tasks']);
    }

    /**
     * Test that tasks can be searched by keyword
     *
     * @return void
     */
    public function test_can_search_tasks_by_keyword(): void
    {
        // Create tasks with specific titles
        Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'title' => 'Search Task One',
            'description' => 'Regular description',
        ]);

        Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'title' => 'Regular Title',
            'description' => 'Search description here',
        ]);

        Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'title' => 'Another Task',
            'description' => 'Another description',
        ]);

        // Search for tasks with keyword "Search"
        $result = $this->taskService->getTasksGroupedByStages(10, null, 'Search');

        // Assert the correct tasks were found
        $this->assertCount(2, $result[TaskStages::BACKLOG->value]['tasks']);

        // Get all tasks without search
        $allTasks = $this->taskService->getTasksGroupedByStages(10);
        $this->assertCount(3, $allTasks[TaskStages::BACKLOG->value]['tasks']);
    }

    /**
     * Test that a task can be created
     *
     * @return void
     */
    public function test_can_create_task(): void
    {
        // Create task DTO
        $taskDto = new CreateTaskDto(
            title: 'Test Task',
            description: 'This is a test task',
            stage: TaskStages::BACKLOG->value,
            user_id: $this->user->id,
            index: 0
        );

        // Create a task
        $task = $this->taskService->createTask($taskDto);

        // Assert the task was created with the correct data
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($taskDto->title, $task->title);
        $this->assertEquals($taskDto->description, $task->description);
        $this->assertEquals(TaskStages::BACKLOG->value, $task->stage);
        $this->assertEquals($this->user->id, $task->user_id);
        $this->assertEquals(0, $task->index); // First task in the backlog should have index 0

        // Assert the task exists in the database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $taskDto->title,
            'description' => $taskDto->description,
            'stage' => TaskStages::BACKLOG->value,
            'user_id' => $this->user->id,
            'index' => 0,
        ]);
    }

    /**
     * Test that a task can be retrieved
     *
     * @return void
     */
    public function test_can_get_task(): void
    {
        // Create a task for the authenticated user
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Get the task
        $retrievedTask = $this->taskService->getTask($task);

        // Assert the task was retrieved
        $this->assertInstanceOf(Task::class, $retrievedTask);
        $this->assertEquals($task->id, $retrievedTask->id);
    }

    /**
     * Test that a task cannot be retrieved by another user
     *
     * @return void
     */
    public function test_cannot_get_task_of_another_user(): void
    {
        // Create a task for another user
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        // Try to get the task
        $retrievedTask = $this->taskService->getTask($task);

        // Assert the task was not retrieved
        $this->assertNull($retrievedTask);
    }

    /**
     * Test that a task can be updated
     *
     * @return void
     */
    public function test_can_update_task(): void
    {
        // Create a task for the authenticated user
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 0,
        ]);

        // Create update DTO
        $updateDto = new UpdateTaskDto(
            title: 'Updated Task',
            description: 'This is an updated task',
            stage: TaskStages::IN_PROGRESS->value,
            index: 0
        );

        // Update the task
        $updatedTask = $this->taskService->updateTask($task, $updateDto);

        // Assert the task was updated
        $this->assertInstanceOf(Task::class, $updatedTask);
        $this->assertEquals($updateDto->title, $updatedTask->title);
        $this->assertEquals($updateDto->description, $updatedTask->description);
        $this->assertEquals($updateDto->stage, $updatedTask->stage);
        $this->assertEquals($updateDto->index, $updatedTask->index);

        // Assert the task was updated in the database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $updateDto->title,
            'description' => $updateDto->description,
            'stage' => $updateDto->stage,
            'index' => $updateDto->index,
        ]);
    }

    /**
     * Test that a task cannot be updated by another user
     *
     * @return void
     */
    public function test_cannot_update_task_of_another_user(): void
    {
        // Create a task for another user
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        // Create update DTO
        $updateDto = new UpdateTaskDto(
            title: 'Updated Task',
            description: 'This is an updated task',
            stage: TaskStages::IN_PROGRESS->value,
            index: 0
        );

        // Try to update the task
        $updatedTask = $this->taskService->updateTask($task, $updateDto);

        // Assert the task was not updated
        $this->assertNull($updatedTask);

        // Assert the task was not updated in the database
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
            'title' => $updateDto->title,
        ]);
    }

    /**
     * Test that a task can be deleted
     *
     * @return void
     */
    public function test_can_delete_task(): void
    {
        // Create a task for the authenticated user
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Delete the task
        $deleted = $this->taskService->deleteTask($task);

        // Assert the task was deleted
        $this->assertTrue($deleted);

        // Assert the task was deleted from the database
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test that a task cannot be deleted by another user
     *
     * @return void
     */
    public function test_cannot_delete_task_of_another_user(): void
    {
        // Create a task for another user
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        // Try to delete the task
        $deleted = $this->taskService->deleteTask($task);

        // Assert the task was not deleted
        $this->assertFalse($deleted);

        // Assert the task was not deleted from the database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test that task indices are properly assigned
     *
     * @return void
     */
    public function test_task_indices_are_properly_assigned(): void
    {
        // Create multiple tasks in the same stage
        $task1 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 0,
        ]);

        $task2 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 1,
        ]);

        $task3 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 2,
        ]);

        // Assert that indices are sequential
        $this->assertEquals(0, $task1->index);
        $this->assertEquals(1, $task2->index);
        $this->assertEquals(2, $task3->index);

        // Move task to a different stage
        $updateDto = new UpdateTaskDto(
            title: $task1->title,
            description: $task1->description,
            stage: TaskStages::IN_PROGRESS->value,
            index: 0
        );

        $updatedTask = $this->taskService->updateTask($task1, $updateDto);

        // Assert that the task was moved to the new stage with index 0
        $this->assertEquals(TaskStages::IN_PROGRESS->value, $updatedTask->stage);
        $this->assertEquals(0, $updatedTask->index);

        // Assert that the remaining tasks in the original stage have their indices updated
        $task2->refresh();
        $task3->refresh();
        $this->assertEquals(0, $task2->index);
        $this->assertEquals(1, $task3->index);
    }

    /**
     * Test that tasks are properly reindexed when a task changes stage
     *
     * @return void
     */
    public function test_tasks_are_reindexed_when_changing_stage(): void
    {
        // Create tasks in backlog stage with indices 0, 1, 2
        $task1 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 0,
        ]);

        $task2 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 1,
        ]);

        $task3 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 2,
        ]);

        // Create tasks in in_progress stage with indices 0, 1
        $task4 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::IN_PROGRESS->value,
            'index' => 0,
        ]);

        $task5 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::IN_PROGRESS->value,
            'index' => 1,
        ]);

        // Move task2 (backlog, index 1) to in_progress with index 0
        $updateDto = new UpdateTaskDto(
            title: $task2->title,
            description: $task2->description,
            stage: TaskStages::IN_PROGRESS->value,
            index: 0
        );

        $this->taskService->updateTask($task2, $updateDto);

        // Refresh models from database
        $task1->refresh();
        $task2->refresh();
        $task3->refresh();
        $task4->refresh();
        $task5->refresh();

        // Check that task2 is now in in_progress stage with index 0
        $this->assertEquals(TaskStages::IN_PROGRESS->value, $task2->stage);
        $this->assertEquals(0, $task2->index);

        // Check that task3 has moved up in backlog (index 1 instead of 2)
        $this->assertEquals(1, $task3->index);

        // Check that task4 has moved down (index 1 instead of 0)
        $this->assertEquals(1, $task4->index);

        // Check that task5 has moved down (index 2 instead of 1)
        $this->assertEquals(2, $task5->index);
    }

    /**
     * Test that tasks are properly reindexed when a task changes index within the same stage
     *
     * @return void
     */
    public function test_tasks_are_reindexed_when_changing_index_within_stage(): void
    {
        // Create tasks in backlog stage with indices 0, 1, 2, 3
        $task1 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 0,
        ]);

        $task2 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 1,
        ]);

        $task3 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 2,
        ]);

        $task4 = Task::factory()->create([
            'user_id' => $this->user->id,
            'stage' => TaskStages::BACKLOG->value,
            'index' => 3,
        ]);

        // Move task1 (index 0) to index 2
        $updateDto = new UpdateTaskDto(
            title: $task1->title,
            description: $task1->description,
            stage: TaskStages::BACKLOG->value,
            index: 2
        );

        $this->taskService->updateTask($task1, $updateDto);

        // Refresh models from database
        $task1->refresh();
        $task2->refresh();
        $task3->refresh();
        $task4->refresh();

        // Check that task1 is now at index 2
        $this->assertEquals(2, $task1->index);

        // Check that task2 and task3 have moved up
        $this->assertEquals(0, $task2->index);
        $this->assertEquals(1, $task3->index);

        // Check that task4 is still at index 3
        $this->assertEquals(3, $task4->index);

        // Now move task4 (index 3) to index 1
        $updateDto = new UpdateTaskDto(
            title: $task4->title,
            description: $task4->description,
            stage: TaskStages::BACKLOG->value,
            index: 1
        );

        $this->taskService->updateTask($task4, $updateDto);

        // Refresh models from database
        $task1->refresh();
        $task2->refresh();
        $task3->refresh();
        $task4->refresh();

        // Check that task4 is now at index 1
        $this->assertEquals(1, $task4->index);

        // Check that task3 and task1 have moved down
        $this->assertEquals(2, $task3->index);
        $this->assertEquals(3, $task1->index);

        // Check that task2 is still at index 0
        $this->assertEquals(0, $task2->index);
    }
}
