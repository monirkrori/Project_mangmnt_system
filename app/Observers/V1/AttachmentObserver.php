<?php

namespace App\Observers\V1;

use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachmentObserver
{
    /**
     * عند حذف المرفق، نحذف الملف من التخزين
     */
    public function deleted(Attachment $attachment): void
    {
        $disk = $attachment->disk;
        $path = $attachment->path;

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
            Log::info('Attachment file deleted from storage', [
                'attachment_id' => $attachment->id,
                'path' => $path
            ]);
        } else {
            Log::warning('Attachment file not found in storage', [
                'attachment_id' => $attachment->id,
                'path' => $path
            ]);
        }
    }
}
