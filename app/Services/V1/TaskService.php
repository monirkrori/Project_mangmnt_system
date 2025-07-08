<?php

namespace App\Services\V1;

use Exception;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskService
{


    /**
     * determine who you can assign task to
     * @param User $authUser
     * @return Collection
     */
    public function getAssignableUsers(User $authUser): Collection
    {
        try {
            if ($authUser->hasRole('admin')) {
                return User::where('id', '!=', $authUser->id)->get();
            }

            // Get team IDs the user is part of
            $teamIds = $authUser->teams->pluck('id');

            if ($teamIds->isEmpty()) {
                throw new Exception('User must belong to at least one team.');
            }

            if ($authUser->hasRole('team_owner')) {
                return User::whereHas('teams', function ($query) use ($teamIds) {
                    $query->whereIn('teams.id', $teamIds);
                })
                    ->whereHas('roles', fn($q) =>
                    $q->whereNotIn('name', ['admin', 'team_owner'])
                    )
                    ->get();
            }

            if ($authUser->hasRole('project_manager')) {
                return User::whereHas('teams', function ($query) use ($teamIds) {
                    $query->whereIn('teams.id', $teamIds);
                })
                    ->whereHas('roles', fn($q) =>
                    $q->where('name', 'member')
                    )
                    ->get();
            }

            throw new Exception('You are not authorized to assign tasks.');

        } catch (Exception $e) {
            Log::warning('Assignable user fetching failed: ' . $e->getMessage(), [
                'user_id' => $authUser->id,
                'user_roles' => $authUser->getRoleNames(),
            ]);

            return  new Collection();
        }
    }


    /**
     * Create a new task
     *
     * @param array $data Task data (name, description, status, priority, etc.)
     * @return Task The newly created task
     * @throws \Exception If task creation fails
     */
    public function createTask(array $data): Task
    {
        try {
            return Task::create($data);
        } catch (Exception $e) {
            Log::error('Failed to create task: ' . $e->getMessage(), ['data' => $data]);
            throw new Exception('Failed to create task: ' . $e->getMessage());
        }
    }

    /**
     * Get a task by ID with optional relationships
     *
     * @param int $id Task ID
     * @param array $with Relationships to eager load
     * @return Task
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception If task retrieval fails
     */
    public function getTaskById(int $id, array $with = []): Task
    {
        try {
            return Task::with($with)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning('Task not found with ID: ' . $id);
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to retrieve task: ' . $e->getMessage(), ['id' => $id]);
            throw new Exception('Failed to retrieve task: ' . $e->getMessage());
        }
    }

    /**
     * Update a task
     *
     * @param Task $task Task instance
     * @param array $data Updated data
     * @return bool True if update was successful
     * @throws \Exception If task update fails
     */
    public function updateTask(Task $task, array $data): bool
    {
        try {
            return $task->update($data);
        } catch (Exception $e) {
            Log::error('Failed to update task: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'data' => $data
            ]);
            throw new Exception('Failed to update task: ' . $e->getMessage());
        }
    }

    /**
     * Delete a task
     *
     * @param Task $task Task instance
     * @return bool True if deletion was successful
     * @throws \Exception If task deletion fails
     */
    public function deleteTask(Task $task): bool
    {
        try {
            return $task->delete();
        } catch (Exception $e) {
            Log::error('Failed to delete task: ' . $e->getMessage(), ['task_id' => $task->id]);
            throw new Exception('Failed to delete task: ' . $e->getMessage());
        }
    }

    /**
     * Get all tasks with pagination and optional filters
     *
     * @param array $filters Filters to apply
     * @param array $with Relationships to eager load
     * @param int $perPage Items per page
     * @return LengthAwarePaginator Paginated tasks
     * @throws \Exception If task retrieval fails
     */
    public function getAllTasks(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = Task::with($with);

            // Apply filters
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['priority'])) {
                $query->where('priority', strtolower($filters['priority']));
            }

            if (isset($filters['project_id'])) {
                $query->where('project_id', $filters['project_id']);
            }

            if (isset($filters['assigned_to'])) {
                $query->where('assigned_to', $filters['assigned_to']);
            }

            return $query->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Failed to retrieve tasks: ' . $e->getMessage(), ['filters' => $filters]);
            throw new Exception('Failed to retrieve tasks: ' . $e->getMessage());
        }
    }

    /**
     * Get overdue tasks
     *
     * @param array $with Relationships to eager load
     * @return Collection Collection of overdue tasks
     * @throws \Exception If task retrieval fails
     */
    public function getOverdueTasks(array $with = []): Collection
    {
        try {
            return Task::overdue()
                ->with($with)
                ->get();
        } catch (Exception $e) {
            Log::error('Failed to retrieve overdue tasks: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Get completed tasks
     *
     * @param array $with Relationships to eager load
     * @return Collection Collection of completed tasks
     * @throws \Exception If task retrieval fails
     */
    public function getCompletedTasks(array $with = []): Collection
    {
        try {
            return Task::with($with)->completed()->get();
        } catch (Exception $e) {
            Log::error('Failed to retrieve completed tasks: ' . $e->getMessage());
            throw new Exception('Failed to retrieve completed tasks: ' . $e->getMessage());
        }
    }

    /**
     * Get high priority tasks
     *
     * @param array $with Relationships to eager load
     * @return Collection Collection of high priority tasks
     * @throws \Exception If task retrieval fails
     */
    public function getHighPriorityTasks(array $with = []): Collection
    {
        try {
            return Task::with($with)->highPriority()->get();
        } catch (Exception $e) {
            Log::error('Failed to retrieve high priority tasks: ' . $e->getMessage());
            throw new Exception('Failed to retrieve high priority tasks: ' . $e->getMessage());
        }
    }


    /**
     * Get task's latest attachment
     *
     * @param Task $task Task instance
     * @return \App\Models\Attachment|null The latest attachment or null
     * @throws \Exception If attachment retrieval fails
     */
    public function getLatestAttachment(Task $task): ?\App\Models\Attachment
    {
        try {
            return $task->latestAttachment;
        } catch (Exception $e) {
            Log::error('Failed to retrieve latest attachment for task: ' . $e->getMessage(), [
                'task_id' => $task->id
            ]);
            throw new Exception('Failed to retrieve latest attachment: ' . $e->getMessage());
        }
    }

    /**
     * Check if task status was changed
     *
     * @param Task $task Task instance
     * @return bool True if status was changed
     * @throws \Exception If status check fails
     */
    public function isStatusChanged(Task $task): bool
    {
        try {
            return $task->isStatusChanged();
        } catch (Exception $e) {
            Log::error('Failed to check task status change: ' . $e->getMessage(), [
                'task_id' => $task->id
            ]);
            throw new Exception('Failed to check task status change: ' . $e->getMessage());
        }
    }

}
