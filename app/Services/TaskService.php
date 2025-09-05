<?php

namespace App\Services;

use App\Dtos\Task\CreateTaskDto;
use App\Dtos\Task\UpdateTaskDto;
use App\Enums\TaskStages;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Task Service
 *
 * Handles all task-related operations
 */
class TaskService
{
    /**
     * Get tasks grouped by stages with pagination
     *
     * @param int         $perPage Number of items per page
     * @param string|null $stage   Optional stage to filter by
     * @param string|null $keyword Optional search keyword to filter tasks by title or description
     *
     * @return array Tasks grouped by stages with pagination
     */
    public function getTasksGroupedByStages(int $perPage = 10, ?string $stage = null, ?string $keyword = null): array
    {
        $userId = Auth::id();
        $stages = $stage ? [TaskStages::tryFrom($stage)] : TaskStages::cases();
        $result = [];

        foreach ($stages as $stageEnum) {
            if ($stageEnum === null) {
                continue;
            }

            $tasks = Task::where('user_id', $userId)
                ->where('stage', $stageEnum->value)
                ->when($keyword, function ($q) use ($keyword) {
                    $q->where(function ($query) use ($keyword) {
                        $query->where('title', 'like', '%'.$keyword.'%')
                            ->orWhere('description', 'like', '%'.$keyword.'%');
                    });
                })
                ->orderBy('index')
                ->paginate($perPage);

            $result[$stageEnum->value] = [
                'name' => $stageEnum->name,
                'tasks' => $tasks->items(),
                'meta' => [
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                ],
            ];
        }

        return $result;
    }

    /**
     * Create a new task
     *
     * @param CreateTaskDto $dto Task data
     *
     * @return Task The created task
     */
    public function createTask(CreateTaskDto $dto): Task
    {
        // Create task data array with default values if needed
        $taskData = $dto->toArray();

        // Set default stage if not provided
        if (empty($taskData['stage'])) {
            $taskData['stage'] = TaskStages::BACKLOG->value;
        }

        // Set user ID to authenticated user
        $taskData['user_id'] = Auth::id();

        // Calculate the next index for this stage if not provided
        if (! isset($taskData['index'])) {
            $taskData['index'] = $this->getNextIndexForStage($taskData['stage']);
        }

        return Task::create($taskData);
    }

    /**
     * Get a specific task if it belongs to the authenticated user
     *
     * @param Task $task The task to retrieve
     *
     * @return Task|null The task if it belongs to the user, null otherwise
     */
    public function getTask(Task $task): ?Task
    {
        if ($task->user_id != Auth::id()) {
            return null;
        }

        return $task;
    }

    /**
     * Update a task
     *
     * @param Task          $task The task to update
     * @param UpdateTaskDto $dto  Update task DTO
     *
     * @return Task|null Updated task or null if the task could not be updated
     *
     * This method handles:
     * - Basic task updates (title, description)
     * - Moving tasks between stages (changing stage value)
     * - Reordering tasks within a stage (changing index value)
     * - Automatically reindexes affected tasks to maintain sequential ordering
     */
    public function updateTask(Task $task, UpdateTaskDto $dto): ?Task
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id != Auth::id()) {
            return null;
        }

        // Store original values
        $oldStage = $task->stage;
        $oldIndex = $task->index;

        // Use a transaction to ensure all index updates happen atomically
        DB::transaction(function () use ($task, $dto, $oldStage, $oldIndex) {
            $userId = Auth::id();
            $newStage = $dto->stage ?? $task->stage;
            $newIndex = $dto->index ?? $task->index;

            // If stage changed
            if ($oldStage !== $newStage) {
                // Reindex tasks in old stage (close the gap)
                Task::where('user_id', $userId)
                    ->where('stage', $oldStage)
                    ->where('index', '>', $oldIndex)
                    ->decrement('index');

                // Reindex tasks in new stage (make space)
                Task::where('user_id', $userId)
                    ->where('stage', $newStage)
                    ->where('index', '>=', $newIndex)
                    ->increment('index');
            }
            // If only index changed within same stage
            elseif ($oldIndex !== $newIndex) {
                if ($oldIndex < $newIndex) {
                    // Moving down the list - shift tasks in between up
                    Task::where('user_id', $userId)
                        ->where('stage', $newStage)
                        ->whereBetween('index', [$oldIndex + 1, $newIndex])
                        ->decrement('index');
                } else {
                    // Moving up the list - shift tasks in between down
                    Task::where('user_id', $userId)
                        ->where('stage', $newStage)
                        ->whereBetween('index', [$newIndex, $oldIndex - 1])
                        ->increment('index');
                }
            }

            // Update the task
            $task->update($dto->toArray());
        });

        return $task;
    }

    /**
     * Delete a task
     *
     * @param Task $task The task to delete
     *
     * @return bool True if the task was deleted, false otherwise
     */
    public function deleteTask(Task $task): bool
    {
        if ($task->user_id != Auth::id()) {
            return false;
        }

        return $task->delete();
    }

    /**
     * Get the next available index for a specific stage
     *
     * @param string $stage The stage to get the next index for
     *
     * @return int The next available index
     */
    private function getNextIndexForStage(string $stage): int
    {
        $userId = Auth::id();
        $maxIndex = Task::where('user_id', $userId)
            ->where('stage', $stage)
            ->max('index');

        return is_null($maxIndex) ? 0 : $maxIndex + 1;
    }
}
