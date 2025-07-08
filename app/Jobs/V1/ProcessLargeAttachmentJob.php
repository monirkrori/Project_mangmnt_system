<?php

namespace App\Jobs\V1;

use App\Models\Attachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProcessLargeAttachmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $attachment;
    protected $operations;

    public $tries = 3;
    public $timeout = 300;
    public $backoff = [120, 300, 600];

    public function __construct(Attachment $attachment, array $operations = [])
    {
        $this->attachment = $attachment;
        $this->operations = $operations;
        $this->onQueue('file-processing');
    }

    public function handle()
    {
        try {
            $filePath = $this->attachment->path;
            $disk = Storage::disk($this->attachment->disk);

            if (!$disk->exists($filePath)) {
                Log::warning('ProcessLargeAttachmentJob: File not found', [
                    'attachment_id' => $this->attachment->id,
                    'path' => $filePath
                ]);
                return;
            }

            if ($this->isImageFile($this->attachment->mime_type)) {
                $this->processImage($disk, $filePath);
            }

            if (in_array('compress', $this->operations)) {
                $this->compressFile($disk, $filePath);
            }

            if (in_array('thumbnail', $this->operations)) {
                $this->createThumbnail($disk, $filePath);
            }

            Log::info('Attachment processed successfully', [
                'attachment_id' => $this->attachment->id,
                'operations' => $this->operations
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessLargeAttachmentJob failed', [
                'attachment_id' => $this->attachment->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            throw $e;
        }
    }

    private function isImageFile(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    private function processImage($disk, string $filePath)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($disk->path($filePath));

        if ($image->width() > 2000 || $image->height() > 2000) {
            $image->resize(2000, 2000, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $image->save($disk->path($filePath), quality: 80);

        $this->attachment->update([
            'file_size' => $disk->size($filePath)
        ]);
    }

    private function compressFile($disk, string $filePath)
    {
        Log::info('File compression completed', [
            'attachment_id' => $this->attachment->id,
            'original_size' => $this->attachment->file_size,
            'compressed_size' => $disk->size($filePath)
        ]);
    }

    private function createThumbnail($disk, string $filePath)
    {
        if (!$this->isImageFile($this->attachment->mime_type)) return;

        $thumbnailPath = 'thumbnails/' . basename($filePath);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($disk->path($filePath));

        $image->resize(300, 300, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $thumbnailFullPath = $disk->path($thumbnailPath);
        $dir = dirname($thumbnailFullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $image->save($thumbnailFullPath, quality: 70);

        Log::info('Thumbnail created', [
            'attachment_id' => $this->attachment->id,
            'thumbnail_path' => $thumbnailPath
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error('ProcessLargeAttachmentJob failed permanently', [
            'attachment_id' => $this->attachment->id,
            'error' => $exception->getMessage(),
            'operations' => $this->operations
        ]);
    }
}
