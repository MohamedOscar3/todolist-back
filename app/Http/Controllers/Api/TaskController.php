<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiResponseInterface;
use App\Dtos\Task\CreateTaskDto;
use App\Dtos\Task\UpdateTaskDto;
use App\Enums\TaskStages;
use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Task Management
 *
 * APIs for managing tasks
 *
 * @authenticated
 */
class TaskController extends Controller
{
    /**
     * API Response service
     *
     * @var ApiResponseInterface
     */
    protected ApiResponseInterface $apiResponse;

    /**
     * Task Service
     *
     * @var TaskService
     */
    protected TaskService $taskService;

    /**
     * Constructor
     *
     * @param ApiResponseInterface $apiResponse
     * @param TaskService          $taskService
     */
    public function __construct(ApiResponseInterface $apiResponse, TaskService $taskService)
    {
        $this->apiResponse = $apiResponse;
        $this->taskService = $taskService;
    }

    /**
     * Get all tasks grouped by stages with pagination
     *
     * Returns tasks grouped by stages with pagination for each stage
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @queryParam stage string Optional. Filter tasks by stage (backlog, in_progress, review, done). Example: backlog
     * @queryParam per_page integer Optional. Number of tasks per page. Default: 10. Example: 15
     * @queryParam keyword string Optional. Search tasks by title or description. Example: important
     *
     * @response {
     *   "success": true,
     *   "message": "Tasks retrieved successfully",
     *   "status_code": 200,
     *   "data": {
     *     "backlog": {
     *       "name": "BACKLOG",
     *       "tasks": [
     *         {
     *           "id": 1,
     *           "title": "Task 1",
     *           "description": "Description for task 1",
     *           "stage": "backlog",
     *           "user_id": 1,
     *           "user": {
     *             "id": 1,
     *             "name": "John Doe"
     *           },
     *           "created_at": "2023-01-01T00:00:00+00:00",
     *           "updated_at": "2023-01-01T00:00:00+00:00"
     *         }
     *       ],
     *       "meta": {
     *         "current_page": 1,
     *         "last_page": 1,
     *         "per_page": 10,
     *         "total": 1
     *       }
     *     }
     *   }
     * }
     *
     * @apiResource App\Http\Resources\TaskResource
     *
     * @apiResourceCollection App\Http\Resources\TaskCollection
     *
     * @apiResourceModel App\Models\Task
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $stage = $request->input('stage');
        $keyword = $request->input('keyword', null);
        $result = $this->taskService->getTasksGroupedByStages($perPage, $stage, $keyword);

        // Transform the data with resources
        foreach ($result as $stage => $data) {
            $result[$stage]['tasks'] = TaskResource::collection(collect($data['tasks']));
        }

        return $this->apiResponse->success('Tasks retrieved successfully', 200, $result);
    }

    /**
     * Create a new task
     *
     * @param TaskRequest $request
     *
     * @return JsonResponse
     *
     * @apiResource App\Http\Resources\TaskResource
     *
     * @apiResourceModel App\Models\Task
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->user()->id;
        $data['index'] = $request->input('index', 0);
        $data['stage'] = TaskStages::BACKLOG->value;
        $dto = new CreateTaskDto(
            title: $data['title'],
            description: $data['description'],
            stage: $data['stage'],
            index: $data['index'],
            user_id: $data['user_id'],
        );
        $task = $this->taskService->createTask($dto);

        return $this->apiResponse->success('Task created successfully', 201, new TaskResource($task));
    }

    /**
     * Get a specific task
     *
     * @param Task $task
     *
     * @return JsonResponse
     *
     * @apiResource App\Http\Resources\TaskResource
     *
     * @apiResourceModel App\Models\Task
     */
    public function show(Task $task): JsonResponse
    {
        $task = $this->taskService->getTask($task);

        if (! $task) {
            return $this->apiResponse->error('Task not found', 404);
        }

        return $this->apiResponse->success('Task retrieved successfully', 200, new TaskResource($task));
    }

    /**
     * Update an existing task
     *
     * @param TaskRequest $request
     * @param Task        $task
     *
     * @return JsonResponse
     *
     * @bodyParam title string required The title of the task. Example: Updated Task Title
     * @bodyParam description string required The description of the task. Example: This is an updated task description
     * @bodyParam stage string The stage of the task (backlog, in_progress, review, done). Example: in_progress
     * @bodyParam index integer The position of the task within its stage (0-based). Used for reordering tasks. Example: 2
     *
     * @apiResource App\Http\Resources\TaskResource
     *
     * @apiResourceModel App\Models\Task
     *
     * @response {
     *   "success": true,
     *   "message": "Task updated successfully",
     *   "status_code": 200,
     *   "data": {
     *     "id": 1,
     *     "title": "Updated Task",
     *     "description": "This is an updated task",
     *     "stage": "in_progress",
     *     "index": 2,
     *     "user_id": 1,
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe"
     *     },
     *     "created_at": "2023-01-01T00:00:00+00:00",
     *     "updated_at": "2023-01-01T00:00:00+00:00"
     *   }
     * }
     */
    public function update(TaskRequest $request, Task $task): JsonResponse
    {
        $dto = new UpdateTaskDto(...$request->validated());
        $updatedTask = $this->taskService->updateTask($task, $dto);

        if (! $updatedTask) {
            return $this->apiResponse->error('Task not found', 404);
        }

        return $this->apiResponse->success('Task updated successfully', 200, new TaskResource($updatedTask));
    }

    /**
     * Delete a task
     *
     * @param Task $task
     *
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $deleted = $this->taskService->deleteTask($task);

        if (! $deleted) {
            return $this->apiResponse->error('Task not found', 404);
        }

        return $this->apiResponse->success('Task deleted successfully', 204);
    }
}
