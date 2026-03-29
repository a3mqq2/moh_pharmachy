<?php

namespace App\Jobs;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\CompanyRepresentative;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAnnouncementEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 600;

    public function __construct(
        public Announcement $announcement
    ) {}

    public function handle(): void
    {
        $query = CompanyRepresentative::query();

        if ($this->announcement->target === 'local') {
            $query->whereHas('companies');
        } elseif ($this->announcement->target === 'foreign') {
            $query->whereHas('foreignCompanies');
        }

        $query->chunk(50, function ($representatives) {
            foreach ($representatives as $representative) {
                try {
                    Mail::to($representative->email)
                        ->send(new AnnouncementMail($this->announcement, $representative->full_name ?? $representative->email));
                } catch (\Exception $e) {
                    Log::error(__('general.log_announcement_send_failed', ['id' => $representative->id]) . ': ' . $e->getMessage());
                }
            }
        });

        $this->announcement->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);

        Log::info(__('general.log_announcement_sent', ['id' => $this->announcement->id]));
    }
}
