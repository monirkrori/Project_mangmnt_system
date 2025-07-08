<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanOldAttachments extends Command
{
    protected $signature = 'app:clean-old-attachments';
    protected $description = 'حذف المرفقات غير المرتبطة والتي مرّ عليها أكثر من 30 يومًا';

    public function handle(): void
    {
        $thresholdDate = Carbon::now()->subDays(30);

        $oldAttachments = Attachment::whereNull('attachable_id')
            ->where('created_at', '<', $thresholdDate)
            ->get();

        $deletedCount = 0;

        foreach ($oldAttachments as $attachment) {
            if (Storage::disk($attachment->disk)->exists($attachment->path)) {
                Storage::disk($attachment->disk)->delete($attachment->path);
                Log::info('📦 Attachment deleted via command', ['id' => $attachment->id]);
            }

            $attachment->delete();
            $deletedCount++;
        }

        $this->info("✅ تم حذف {$deletedCount} مرفق غير مستخدم.");
    }
}
