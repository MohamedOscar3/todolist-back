<?php

namespace App\Dtos\Task;

/**
 * Data Transfer Object for updating an existing task
 *
 * @property string|null $title       Task title
 * @property string|null $description Task description
 * @property string|null $stage       Task stage
 * @property int|null    $index       Task index within its stage
 */
class UpdateTaskDto
{
    /**
     * @param string|null $title       Task title
     * @param string|null $description Task description
     * @param string|null $stage       Task stage
     * @param int|null    $index       Task index within its stage
     */
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $stage = null,
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
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            stage: $data['stage'] ?? null,
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
        $result = [];

        if ($this->title !== null) {
            $result['title'] = $this->title;
        }

        if ($this->description !== null) {
            $result['description'] = $this->description;
        }

        if ($this->stage !== null) {
            $result['stage'] = $this->stage;
        }

        if ($this->index !== null) {
            $result['index'] = $this->index;
        }

        return $result;
    }
}
