<?php

namespace App\Services\V1;

use App\Jobs\V1\ProcessLargeAttachmentJob;
use App\Models\Attachment;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentService
{
    /**
     * Upload a file to a given storage disk
     *
     * @param UploadedFile $file       The uploaded file
     * @param mixed $attachable        The model to associate with (e.g. Task, Project)
     * @param string $disk             The storage disk to use (private or public)
     * @return string                  The stored file path
     * @throws Exception               If storing fails
     */
    public function uploadFileToDisk(UploadedFile $file, $attachable, string $disk): string
    {
        $folder = 'attachments/' . Str::kebab(class_basename($attachable)) . '/' . $attachable->id;
        $uniqueName = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs($folder, $uniqueName, $disk);
        if ($path === false) {
            throw new Exception('Failed to store the file');
        }

        return $path;
    }

    /**
     * Create attachment record in the database
     *
     * @param string $path             Path of stored file
     * @param string $disk             Storage disk used
     * @param UploadedFile $file       The uploaded file
     * @param mixed $attachable        The model to associate with
     * @return Attachment              The created attachment
     */
    public function createAttachmentRecord(
        string $path,
        string $disk,
        UploadedFile $file,
        $attachable
    ): Attachment {
        return $attachable->attachments()->create([
            'path' => $path,
            'disk' => $disk,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'attachable_id' => $attachable->id,
            'attachable_type' => get_class($attachable),
        ]);
    }

    /**
     * Upload and store file + create attachment record
     *
     * @param UploadedFile $file       The uploaded file
     * @param mixed $attachable        The model to associate with
     * @param string $disk             Storage disk to use (private/public)
     * @return Attachment
     * @throws Exception               On failure
     */
    public function uploadFile(UploadedFile $file, $attachable, string $disk = 'private'): Attachment
    {
        try {
            $path = $this->uploadFileToDisk($file, $attachable, $disk);

            $attachment = $this->createAttachmentRecord($path, $disk, $file, $attachable);


            ProcessLargeAttachmentJob::dispatch($attachment, ['compress', 'thumbnail']);

            return $attachment;

        } catch (Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'attachable' => $attachable ? get_class($attachable) . '#' . $attachable->id : null,
                'disk' => $disk
            ]);
            throw new Exception('Failed to upload file: ' . $e->getMessage());
        }
    }

    /**
     * Download a file securely from storage
     *
     * @param Attachment $attachment The attachment to download
     * @return StreamedResponse
     * @throws Exception If file download fails
     */
    public function downloadFile(Attachment $attachment): StreamedResponse
    {
        try {
            if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
                throw new Exception('File not found in storage');
            }

            return Storage::disk($attachment->disk)->download(
                $attachment->path,
                $attachment->file_name,
                ['Content-Type' => $attachment->mime_type]
            );

        } catch (Exception $e) {
            Log::error('File download failed: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id,
                'path' => $attachment->path
            ]);
            throw new Exception('Failed to download file: ' . $e->getMessage());
        }
    }

    /**
     * Delete a file and its database record
     *
     * @param Attachment $attachment The attachment to delete
     * @return bool True if deletion was successful
     * @throws Exception If deletion fails
     */
    public function deleteFile(Attachment $attachment): bool
    {
        try {
            $path = $attachment->path;
            $disk = $attachment->disk;

            if ($attachment->delete()) {
                if (Storage::disk($disk)->exists($path)) {
                    return Storage::disk($disk)->delete($path);
                }
                return true;
            }

            throw new Exception('Failed to delete attachment record');

        } catch (Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id,
                'path' => $attachment->path
            ]);
            throw new Exception('Failed to delete file: ' . $e->getMessage());
        }
    }

    /**
     * Get the contents of a file
     *
     * @param Attachment $attachment The attachment to read
     * @return string The file contents
     * @throws Exception If file cannot be read
     */
    public function getFileContents(Attachment $attachment): string
    {
        try {
            if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
                throw new Exception('File not found in storage');
            }

            return Storage::disk($attachment->disk)->get($attachment->path);

        } catch (Exception $e) {
            Log::error('Failed to get file contents: ' . $e->getMessage(), [
                'attachment_id' => $attachment->id,
                'path' => $attachment->path
            ]);
            throw new Exception('Failed to read file: ' . $e->getMessage());
        }
    }
}
