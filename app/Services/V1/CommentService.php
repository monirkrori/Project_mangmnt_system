<?php

namespace App\Services\V1;

use App\Models\Comment;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CommentService
{
    /**
     * Create a new comment and associate it with a commentable model
     *
     * @param array $data Comment data including 'content'
     * @param User $user The user creating the comment
     * @param Model $commentable The model instance being commented on
     * @return Comment The newly created comment
     * @throws Exception If comment creation fails
     */
    public function createComment(array $data, User $user, Model $commentable): Comment
    {
        try {
            $comment = Comment::create([
                'content' => $data['content'],
                'user_id' => $user->id,
                'commentable_id' => $commentable->id,
                'commentable_type' => get_class($commentable)
            ]);

            return $comment->load('user');

        } catch (Exception $e) {
            Log::error('Failed to create comment', [
                'user_id' => $user->id,
                'commentable' => get_class($commentable) . '#' . $commentable->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to create comment: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing comment's content
     *
     * @param Comment $comment The comment to update
     * @param array $data New comment data including 'content'
     * @return Comment The updated comment
     * @throws Exception If comment update fails
     */
    public function updateComment(Comment $comment, array $data): Comment
    {
        try {
            $comment->update(['content' => $data['content']]);
            return $comment->fresh()->load('user');

        } catch (Exception $e) {
            Log::error('Failed to update comment', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to update comment: ' . $e->getMessage());
        }
    }

    /**
     * Delete a comment
     *
     * @param Comment $comment The comment to delete
     * @return bool True if deletion was successful
     * @throws Exception If comment deletion fails
     */
    public function deleteComment(Comment $comment): bool
    {
        try {
            return $comment->delete();

        } catch (Exception $e) {
            Log::error('Failed to delete comment', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to delete comment: ' . $e->getMessage());
        }
    }

    /**
     * Get paginated comments for a commentable model
     *
     * @param Model $commentable The model instance to get comments for
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated comments
     * @throws Exception If comments retrieval fails
     */
    public function getCommentsFor(Model $commentable, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Comment::with(['user', 'commentable'])
                ->where('commentable_id', $commentable->id)
                ->where('commentable_type', get_class($commentable))
                ->latestFirst()
                ->paginate($perPage);

        } catch (Exception $e) {
            Log::error('Failed to retrieve comments', [
                'commentable' => get_class($commentable) . '#' . $commentable->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to retrieve comments: ' . $e->getMessage());
        }
    }

    /**
     * Get a single comment with its relations
     *
     * @param int $id The comment ID
     * @return Comment The comment with loaded relations
     * @throws ModelNotFoundException If comment is not found
     * @throws Exception If comment retrieval fails
     */
    public function getCommentWithRelations(int $id): Comment
    {
        try {
            return Comment::with(['user', 'commentable'])
                ->findOrFail($id);

        } catch (ModelNotFoundException $e) {
            Log::warning('Comment not found', ['comment_id' => $id]);
            throw $e;

        } catch (Exception $e) {
            Log::error('Failed to retrieve comment', [
                'comment_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to retrieve comment: ' . $e->getMessage());
        }
    }

    /**
     * Count comments for a specific commentable model
     *
     * @param Model $commentable The model instance
     * @return int Number of comments
     * @throws Exception If count operation fails
     */
    public function countCommentsFor(Model $commentable): int
    {
        try {
            return Comment::where('commentable_id', $commentable->id)
                ->where('commentable_type', get_class($commentable))
                ->count();

        } catch (Exception $e) {
            Log::error('Failed to count comments', [
                'commentable' => get_class($commentable) . '#' . $commentable->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to count comments: ' . $e->getMessage());
        }
    }
}
