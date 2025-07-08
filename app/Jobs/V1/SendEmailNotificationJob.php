<?php
namespace App\Jobs\V1;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $mailableClass;
    protected $mailableData;

    public $tries = 3;
    public $backoff = [60, 120, 300];
    public $timeout = 120;

    public function __construct(User $user, string $mailableClass, array $mailableData = [])
    {
        $this->user = $user;
        $this->mailableClass = $mailableClass;
        $this->mailableData = $mailableData;

        $this->onQueue('emails');
    }

    public function handle()
    {
        try {
            if (!$this->user || !$this->user->email) {
                Log::warning('SendEmailNotificationJob: User or email not found', [
                    'user_id' => $this->user->id ?? 'null'
                ]);
                return;
            }

            $mailable = new $this->mailableClass(...$this->mailableData);

            Mail::to($this->user->email)->send($mailable);

            Log::info('Email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'mailable' => $this->mailableClass
            ]);

        } catch (\Exception $e) {
            Log::error('SendEmailNotificationJob failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }
}
