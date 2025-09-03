<?php

namespace Tests\Feature\Api;

use App\Enums\TaskStages;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task Controller Test
 *
 * Tests for the Task API endpoints
 */
class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user
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

        // Create a test user
        $this->user = User::factory()->create();
    }

    /**
     * Test that tasks can be retrieved grouped by stages with pagination
     *
     * @return void
     */
    public function test_index_returns_tasks_grouped_by_stages(): void
    {
        // Create tasks for each stage
        Task::factory(5)->backlog()->create(['user_id' => $this->user->id]);
        Task::factory(3)->inProgress()->create(['user_id' => $this->user->id]);
        Task::factory(2)->review()->create(['user_id' => $this->user->id]);
        Task::factory(4)->done()->create(['user_id' => $this->user->id]);

        // Create tasks for another user (should not be returned)
        $anotherUser = User::factory()->create();
        Task::factory(3)->create(['user_id' => $anotherUser->id]);

        // Make the request as the authenticated user
        $response = $this->actingAs($this->user)
            ->getJson('/api/tasks');

        // Assert successful response
        $response->assertStatus(200);

        // Get the response content for debugging
        $responseContent = $response->getContent();
        $this->assertNotEmpty($responseContent);

        // Decode the response content
        $responseData = json_decode($responseContent, true);

        // Check that the response has success and message keys
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);

        // Get the data part of the response
        $data = $responseData['data'];

        // Check that the data contains the expected stages
        $this->assertArrayHasKey(TaskStages::BACKLOG->value, $data);
        $this->assertArrayHasKey(TaskStages::IN_PROGRESS->value, $data);
        $this->assertArrayHasKey(TaskStages::REVIEW->value, $data);
        $this->assertArrayHasKey(TaskStages::DONE->value, $data);

        // Check that each stage has tasks and meta information
        foreach (TaskStages::cases() as $stage) {
            $this->assertArrayHasKey('tasks', $data[$stage->value]);
            $this->assertArrayHasKey('meta', $data[$stage->value]);
        }

        // Assert correct counts for each stage
        $this->assertEquals(5, $data[TaskStages::BACKLOG->value]['meta']['total']);
        $this->assertEquals(3, $data[TaskStages::IN_PROGRESS->value]['meta']['total']);
        $this->assertEquals(2, $data[TaskStages::REVIEW->value]['meta']['total']);
        $this->assertEquals(4, $data[TaskStages::DONE->value]['meta']['total']);

        // Check that each task has an index field
        foreach ($data[TaskStages::BACKLOG->value]['tasks'] as $task) {
            $this->assertArrayHasKey('index', $task);
        }
    }

    /**
     * Test that tasks can be searched by keyword
     *
     * @return void
     */
    public function test_index_can_search_tasks_by_keyword(): void
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

        // Make the request with search parameter
        $response = $this->actingAs($this->user)
            ->getJson('/api/tasks?keyword=Search');

        // Assert successful response
        $response->assertStatus(200);

        // Get the response data
        $responseData = $response->json('data');

        // Assert correct counts for search results
        $this->assertEquals(2, $responseData[TaskStages::BACKLOG->value]['meta']['total']);

        // Check that the tasks contain the search term
        $tasks = $responseData[TaskStages::BACKLOG->value]['tasks'];
        $this->assertCount(2, $tasks);

        // Verify that each task has the index field
        foreach ($tasks as $task) {
            $this->assertArrayHasKey('index', $task);
        }
    }

    /**
     * Test that a task can be created
     *
     * @return void
     */
    public function test_store_creates_a_task(): void
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'This is a test task',
        ];

        // Make the request as the authenticated user
        $response = $this->actingAs($this->user)
            ->postJson('/api/tasks', $taskData);

        // Assert successful response
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'stage',
                'user_id',
                'created_at',
                'updated_at',
            ],
        ]);

        // Assert the task was created with the correct data
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'stage' => TaskStages::BACKLOG->value,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test that a task can be retrieved
     *
     * @return void
     */
    public function test_show_returns_a_task(): void
    {
        // Create a task
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        // Make the request as the authenticated user
        $response = $this->actingAs($this->user)
            ->getJson("/api/tasks/{$task->id}");

        // Assert successful response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'stage',
                'index',
                'user_id',
                'created_at',
                'updated_at',
            ],
        ]);

        // Assert the correct task was returned
        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'stage' => $task->stage,
                'index' => $task->index,
                'user_id' => $task->user_id,
            ],
        ]);
    }

    /**
     * Test that a task cannot be retrieved by another user
     *
     * @return void
     */
    public function test_show_returns_404_for_another_users_task(): void
    {
        // Create a task for another user
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $anotherUser->id]);

        // Make the request as the authenticated user
        $response = $this->actingAs($this->user)
            ->getJson("/api/tasks/{$task->id}");

        // Assert not found response
        $response->assertStatus(404);
    }

    /**
     * Test that a task can be updated
     *
     * @return void
     */
    public function test_update_updates_a_task(): void
    {
        // Create a task
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updatedData = [
            'title' => 'Updated Task',
            'description' => 'This is an updated task',
            'stage' => TaskStages::IN_PROGRESS->value,
        ];

        // Make the request as the authenticated user
        $response = $this->actingAs($this->user)
            ->putJson("/api/tasks/{$task->id}", $updatedData);

        // Assert successful response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'stage',
                'index',
                'user_id',
                'created_at',
                'updated_at',
            ],
        ]);

        // Assert the task was updated with the correct data
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'description' => 'This is an updated task',
            'stage' => TaskStages::IN_PROGRESS->value,
            'user_id' => $this->user->id,
        ]);

        // Get the updated task and check that it has an index
        $updatedTask = Task::find($task->id);
        $this->assertNotNull($updatedTask->index);
    }

    /**
     * Test that a task cannot be updated by another user
     *
     * @return void
     */
    public function test_update_returns_404_for_another_users_task(): void
    {
        // Create a task for another user
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $anotherUser->id]);

        $updatedData = [
            'title' => 'Updated Task',
            'description' => 'This is an updated task',
            'stage' => TaskStages::IN_PROGRESS->value,
        ];

        // Make the request as the authenticated user
        $response = $this->actingAs($this->user)
            ->putJson("/api/tasks/{$task->id}", $updatedData);

        // Assert not found response
        $response->assertStatus(404);

        // Assert the task was not updated
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'stage' => $task->stage,
            'user_id' => $anotherUser->id,
        ]);
    }

    /**
     * Test that a task can be deleted
     *
     * @return void
     */
    public function test_destroy_deletes_a_task(): void
    {
        // Create a task
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        // Make the request as the authenticated user
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tasks/{$task->id}");

        // Assert successful response
        $response->assertStatus(204);

        // Assert the task was deleted
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test that a task cannot be deleted by another user
     *
     * @return void
     */
    public function test_destroy_returns_404_for_another_users_task(): void
    {
        // Create a task for another user
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $anotherUser->id]);

        // Make the request as the authenticated user
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tasks/{$task->id}");

        // Assert not found response
        $response->assertStatus(404);

        // Assert the task was not deleted
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
