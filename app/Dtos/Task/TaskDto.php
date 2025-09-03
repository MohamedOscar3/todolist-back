<?php

namespace App\Dtos\Task;

use App\Models\Task;

/**
 * Data Transfer Object for task data
 *
 * @property int    $id          Task ID
 * @property string $title       Task title
 * @property string $description Task description
 * @property string $stage       Task stage
 * @property int    $index       Task index within its stage
 * @property int    $user_id     Associated user ID
 * @property string $created_at  Creation timestamp
 * @property string $updated_at  Update timestamp
 */
class TaskDto
{
    /**
     * @param int    $id          Task ID
     * @param string $title       Task title
     * @param string $description Task description
     * @param string $stage       Task stage
     * @param int    $index       Task index within its stage
     * @param int    $user_id     Associated user ID
     * @param string $created_at  Creation timestamp
     * @param string $updated_at  Update timestamp
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $stage,
        public readonly int $index,
        public readonly int $user_id,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {}

    /**
     * Create DTO from Task model
     *
     * @param Task $task Task model
     *
     * @return self
     */
    public static function fromModel(Task $task): self
    {
        return new self(
            id: $task->id,
            title: $task->title,
            description: $task->description,
            stage: $task->stage,
            index: $task->index,
            user_id: $task->user_id,
            created_at: $task->created_at->toIso8601String(),
            updated_at: $task->updated_at->toIso8601String(),
        );
    }

    /**
     * Convert DTO to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'stage' => $this->stage,
            'index' => $this->index,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
