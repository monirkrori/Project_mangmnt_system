<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\V1\Task\TaskAssigned;
use App\Events\V1\Task\TaskStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Task\StoreTaskRequest;
use App\Http\Requests\V1\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Services\V1\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{


    /**
     * @var TaskService
     */
    protected $taskService;

    /**
     * TaskController constructor
     *
     * @param TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of tasks
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'priority', 'project_id', 'assigned_to']);
        $tasks = $this->taskService->getAllTasks($filters, ['project', 'assignedTo', 'createdBy']);

        return $this->successResponse($tasks);
    }

    /**
     * Store a newly created task
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $authUser = Auth::user();

        $assignableUsers = $this->taskService->getAssignableUsers($authUser);
        $assignedToId = $request->input('assigned_to');


        if ($assignedToId && !$assignableUsers->pluck('id')->contains($assignedToId)) {

            return $this->errorResponse(
                'You are not authorized to assign this task to the selected user.',
                403
            );

        }

        $task = $this->taskService->createTask($request->validated());

        TaskAssigned::dispatch($task);

        return $this->successResponse($task, 'Task created successfully', 201);

    }


    /**
     * Display the specified task
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task = $this->taskService->getTaskById($task->id, ['project', 'assignedTo', 'createdBy', 'comments', 'attachments']);

        return $this->successResponse($task);
    }

    /**
     * Update the specified task
     *
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updated = $this->taskService->updateTask($task, $request->validated());

        if (!$updated) {
            return $this->errorResponse('Failed to update task', 500);
        }

        if ($this->taskService->isStatusChanged($task)) {
            event(new TaskStatusUpdated($task));
        }

        return $this->successResponse($task->fresh(), 'Task updated successfully');
    }

    /**
     * Remove the specified task
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $deleted = $this->taskService->deleteTask($task);

        if (!$deleted) {
            return $this->errorResponse('Failed to delete task', 500);
        }

        return $this->successResponse(null, 'Task deleted successfully');
    }

    /**
     * Get overdue tasks
     *
     * @return JsonResponse
     */
    public function overdue(): JsonResponse
    {
        $tasks = $this->taskService->getOverdueTasks([]);

        if (!$tasks) {
            return $this->successResponse([], 'No overdue tasks found');
        }
        return $this->successResponse($tasks);
    }

    /**
     * Get completed tasks
     *
     * @return JsonResponse
     */
    public function completed(): JsonResponse
    {
        $tasks = $this->taskService->getCompletedTasks(['project', 'assignedTo']);
        if (!$tasks) {
            return $this->successResponse([], 'No completed tasks found');
        }
        return $this->successResponse($tasks);
    }

    /**
     * Get high priority tasks
     *
     * @return JsonResponse
     */
    public function highPriority(): JsonResponse
    {
        $tasks = $this->taskService->getHighPriorityTasks(['project', 'assignedTo']);
        if (!$tasks) {
            return $this->successResponse([], 'No high priority tasks found');
        }
        return $this->successResponse($tasks);
    }


    /**
     * Get task's latest attachment
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function latestAttachment(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $attachment = $this->taskService->getLatestAttachment($task);

        if (!$attachment) {
            return $this->successResponse([], 'No attachment tasks found');
        }

        return $this->successResponse($attachment);
    }


}
