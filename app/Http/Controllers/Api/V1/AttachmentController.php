<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Attachment\StoreAttachmentRequest;
use App\Http\Requests\V1\Attachment\UpdateAttachmentRequest;
use App\Models\Attachment;
use App\Models\Task;
use App\Services\V1\AttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * The attachment service instance
     *
     * @var AttachmentService
     */
    private AttachmentService $attachmentService;

    /**
     * Create a new controller instance
     *
     * @param AttachmentService $attachmentService
     */
    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    /**
     * Helper method to map route param to model
     */
    private function resolveAttachableClass(string $type): string
    {
        return match (Str::lower($type)) {
            'task', 'tasks' => \App\Models\Task::class,
            'comment', 'comments' => \App\Models\Comment::class,
            'project', 'projects' => \App\Models\Project::class,
            default => abort(404, 'Unsupported attachable type'),
        };
    }

    /**
     * Upload a new attachment to a task
     *
     * @param StoreAttachmentRequest $request The validated request containing the file
     * @param Task $task The task to attach the file to
     * @return JsonResponse
     */
    public function store(StoreAttachmentRequest $request, string $type, int $id): JsonResponse
    {
        if (!$request->hasFile('file')) {
            return $this->errorResponse('No file was uploaded', 400);
        }

        // Resolve model dynamically from type
        $modelClass = $this->resolveAttachableClass($type);
        $attachable = $modelClass::findOrFail($id);

        // Get disk type from request or consider it private by default
        $disk = $request->input('disk', 'private');
        if (!in_array($disk, ['private', 'public'])) {
            return $this->errorResponse('Invalid disk type. Allowed: private, public.', 422);
        }

        // Upload the file and create attachment
        $attachment = $this->attachmentService->uploadFile(
            $request->file('file'),
            $attachable,
            $disk
        );

        return $this->successResponse($attachment, 'File uploaded successfully', 201);
    }


    /**
     * Update attachment metadata (file name)
     *
     * @param UpdateAttachmentRequest $request The validated request
     * @param Attachment $attachment The attachment to update
     * @return JsonResponse
     */
    public function update(UpdateAttachmentRequest $request, Attachment $attachment): JsonResponse
    {

        if ($request->has('file_name') && $request->file_name !== $attachment->file_name) {
            $attachment->update($request->only(['file_name']));
        }

        return $this->successResponse(
            $attachment->fresh(),
            'Attachment updated successfully'
        );
    }

    /**
     * Download an attachment file
     *
     * @param Attachment $attachment The attachment to download
     * @return StreamedResponse
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If file not found
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException If unauthorized
     */
    public function download(Attachment $attachment): StreamedResponse
    {
        return $this->attachmentService->downloadFile($attachment);
    }

    /**
     * Delete an attachment
     *
     * @param Attachment $attachment The attachment to delete
     * @return JsonResponse
     *
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        $this->authorize('delete', $attachment);

        $this->attachmentService->deleteFile($attachment);

        return $this->successResponse(
            null,
            'Attachment deleted successfully'
        );
    }

    /**
     * Get attachment metadata
     *
     * @param Attachment $attachment The attachment to view
     * @return JsonResponse
     *
     */
    public function show(Attachment $attachment): JsonResponse
    {
        // Authorization is handled by the policy check
        $this->authorize('view', $attachment);

        return $this->successResponse($attachment);
    }
}
