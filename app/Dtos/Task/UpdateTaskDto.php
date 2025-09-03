<?php

namespace App\Dtos\Task;

use App\Enums\TaskStages;

/**
 * Data Transfer Object for updating an existing task
 *
 * @property string     $title       Task title
 * @property string     $description Task description
 * @property TaskStages $stage       Task stage
 * @property int        $index       Task index within its stage
 */
class UpdateTaskDto
{
    /**
     * @param string $title       Task title
     * @param string $description Task description
     * @param string $stage       Task stage
     * @param int    $index       Task index within its stage
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $stage,
        public readonly ?int $index = null,
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
            index: $data['index'] ?? null,
        );
    }

    /**
     * Convert DTO to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'title' => $this->title,
            'description' => $this->description,
            'stage' => $this->stage,
        ];

        if ($this->index !== null) {
            $result['index'] = $this->index;
        }

        return $result;
    }
}
