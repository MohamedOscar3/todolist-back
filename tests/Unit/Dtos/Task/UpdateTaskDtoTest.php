<?php

namespace Tests\Unit\Dtos\Task;

use App\Dtos\Task\UpdateTaskDto;
use App\Enums\TaskStages;
use Tests\TestCase;

/**
 * Update Task DTO Test
 *
 * Unit tests for the UpdateTaskDto class
 */
class UpdateTaskDtoTest extends TestCase
{
    /**
     * Test that an UpdateTaskDto can be created from an array
     *
     * @return void
     */
    public function test_can_create_from_array(): void
    {
        // Create data array
        $data = [
            'title' => 'Updated Task',
            'description' => 'Updated Description',
            'stage' => TaskStages::IN_PROGRESS->value,
        ];

        // Create DTO from array
        $dto = UpdateTaskDto::fromArray($data);

        // Assert DTO properties match array values
        $this->assertEquals($data['title'], $dto->title);
        $this->assertEquals($data['description'], $dto->description);
        $this->assertEquals($data['stage'], $dto->stage);
    }

    /**
     * Test that an UpdateTaskDto can be converted to an array
     *
     * @return void
     */
    public function test_can_convert_to_array(): void
    {
        // Create DTO
        $dto = new UpdateTaskDto(
            title: 'Updated Task',
            description: 'Updated Description',
            stage: TaskStages::REVIEW->value,
        );

        // Convert DTO to array
        $array = $dto->toArray();

        // Assert array contains expected keys and values
        $this->assertIsArray($array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('stage', $array);

        $this->assertEquals('Updated Task', $array['title']);
        $this->assertEquals('Updated Description', $array['description']);
        $this->assertEquals(TaskStages::REVIEW->value, $array['stage']);
    }
}
