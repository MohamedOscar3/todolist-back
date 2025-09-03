<?php

namespace Tests\Unit\Dtos\Task;

use App\Dtos\Task\CreateTaskDto;
use App\Enums\TaskStages;
use Tests\TestCase;

/**
 * Create Task DTO Test
 *
 * Unit tests for the CreateTaskDto class
 */
class CreateTaskDtoTest extends TestCase
{
    /**
     * Test that a CreateTaskDto can be created from an array
     *
     * @return void
     */
    public function test_can_create_from_array(): void
    {
        // Create data array
        $data = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'stage' => TaskStages::BACKLOG->value,
            'user_id' => 1,
        ];

        // Create DTO from array
        $dto = CreateTaskDto::fromArray($data);

        // Assert DTO properties match array values
        $this->assertEquals($data['title'], $dto->title);
        $this->assertEquals($data['description'], $dto->description);
        $this->assertEquals($data['stage'], $dto->stage);
        $this->assertEquals($data['user_id'], $dto->user_id);
    }

    /**
     * Test that a CreateTaskDto can be converted to an array
     *
     * @return void
     */
    public function test_can_convert_to_array(): void
    {
        // Create DTO
        $dto = new CreateTaskDto(
            title: 'Test Task',
            description: 'Test Description',
            stage: TaskStages::BACKLOG->value,
            user_id: 1,
            index: 0,
        );

        // Convert DTO to array
        $array = $dto->toArray();

        // Assert array contains expected keys and values
        $this->assertIsArray($array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('stage', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('index', $array);

        $this->assertEquals('Test Task', $array['title']);
        $this->assertEquals('Test Description', $array['description']);
        $this->assertEquals(TaskStages::BACKLOG->value, $array['stage']);
        $this->assertEquals(1, $array['user_id']);
    }
}
