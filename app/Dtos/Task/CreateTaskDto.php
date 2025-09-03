<?php

namespace App\Dtos\Task;

use App\Enums\TaskStages;

/**
 * Data Transfer Object for creating a new task
 *
 * @property string     $title       Task title
 * @property string     $description Task description
 * @property TaskStages $stage       Task stage
 * @property int        $index       Task index within its stage
 * @property int        $user_id     Associated user ID
 */
class CreateTaskDto
{
    /**
     * @param string $title       Task title
     * @param string $description Task description
     * @param string $stage       Task stage
     * @param int    $user_id     Associated user ID
     * @param int    $index       Task index within its stage
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $stage,
        public readonly int $user_id,
        public readonly int $index = 0,
    ) {}

    /**
     * Create DTO from request data
     *
     * @param array $data Request data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            stage: $data['stage'],
            index: $data['index'] ?? 0,
            user_id: $data['user_id'],
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
            'title' => $this->title,
            'description' => $this->description,
            'stage' => $this->stage,
            'index' => $this->index,
            'user_id' => $this->user_id,
        ];
    }
}
