<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\V1\CommentCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Comment\StoreCommentRequest;
use App\Http\Requests\V1\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Services\V1\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    /**
     * The comment service instance
     *
     * @var CommentService
     */
    private CommentService $commentService;

    /**
     * Create a new controller instance
     *
     * @param CommentService $commentService
     */
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Helper method to map route param to model
     */
    private function resolveCommentableClass(string $type): string
    {
        return match (Str::lower($type)) {
            'task', 'tasks' => \App\Models\Task::class,
            'project', 'projects' => \App\Models\Project::class,
            default => abort(404, 'Unsupported commentable type'),
        };
    }
    /**
     * Get paginated comments for a specific task
     *
     * @param string $type and int $id The commentable to get comments for
     * @return JsonResponse
     *
     */
    public function index(string $type, int $id): JsonResponse
    {
        $modelClass = $this->resolveCommentableClass($type);
        $commentable = $modelClass::findOrFail($id);

        $comments = $this->commentService->getCommentsFor($commentable);

        return $this->successResponse($comments);
    }

    /**
     * Store a new comment on a task
     *
     * @param StoreCommentRequest $request The validated request
     * @param string $type and int $id of The commentable to comment on
     * @return JsonResponse
     *
     */
    public function store(StoreCommentRequest $request, string $type, int $id): JsonResponse
    {


        $modelClass = $this->resolveCommentableClass($type);
        $commentable = $modelClass::findOrFail($id);

        $comment = $this->commentService->createComment(
            $request->validated(),
            $request->user(),
            $commentable
        );

        if ($comment->commentable_type === 'App\\Models\\Task') {
            $comment->load('commentable');
            CommentCreated::dispatch($comment);
        }



        return $this->successResponse(
            $comment->load('user'),
            'Comment added successfully',
            201
        );
    }

    /**
     * Update an existing comment
     *
     * @param UpdateCommentRequest $request The validated request
     * @param Comment $comment The comment to update
     * @return JsonResponse
     *
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        // Only update if content actually changed
        if ($request->input('content') === $comment->content) {
            return $this->successResponse($comment, 'Comment content unchanged');
        }

        $comment = $this->commentService->updateComment($comment, $request->validated());
        return $this->successResponse($comment, 'Comment updated successfully');
    }

    /**
     * Delete a comment
     *
     * @param Comment $comment The comment to delete
     * @return JsonResponse
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);
        return $this->successResponse(null, 'Comment deleted successfully');
    }

    /**
     * Get a single comment with its relationships
     *
     * @param Comment $comment The comment to retrieve
     * @return JsonResponse
     */
    public function show(Comment $comment): JsonResponse
    {
        $comment = $this->commentService->getCommentWithRelations($comment->id);
        return $this->successResponse($comment);
    }

    /**
     * Get comment count for a specific task
     *
     * @param string $type and int $id of The commentable to count comments for
     * @return JsonResponse
     *
     */
    public function count(string $type, int $id): JsonResponse
    {
        $modelClass = $this->resolveCommentableClass($type);
        $commentable = $modelClass::findOrFail($id);

        $count = $this->commentService->countCommentsFor($commentable);

        return $this->successResponse([
            'count' => $count,
            'commentable_type' => $type,
            'commentable_id' => $id
        ]);
    }
}
