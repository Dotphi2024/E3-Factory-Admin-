<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BatchSchedule;
use App\Models\Participant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendSessionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:send-whatsapp-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated WhatsApp session reminders for upcoming sessions to active participants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $schedules = BatchSchedule::with(['batch.batchParticipants'])
            ->whereIn('date', [$today, $tomorrow])
            ->where('is_session_completed', '!=', 1)
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('No upcoming sessions scheduled for today or tomorrow.');
            return 0;
        }

        $totalSent = 0;

        foreach ($schedules as $schedule) {
            $batch = $schedule->batch;
            if (!$batch || !$batch->is_active) {
                continue;
            }

            $participants = $batch->batchParticipants()->where('is_active', 1)->get();
            if ($participants->isEmpty()) {
                continue;
            }

            $sessionDate = Carbon::parse($schedule->date)->format('d-m-Y');
            $sessionName = $schedule->name;
            $batchName = $batch->name;

            $this->info("Processing session '{$sessionName}' (Batch: {$batchName}) on {$sessionDate} for " . $participants->count() . " participant(s)...");

            foreach ($participants as $participant) {
                $mobile = preg_replace('/[^0-9]/', '', $participant->mobile);
                if (empty($mobile)) continue;

                if (strlen($mobile) === 10) {
                    $mobile = '91' . $mobile;
                }

                $message = "Dear {$participant->first_name},\n\n" .
                           "This is an automated reminder for your upcoming session:\n" .
                           "📌 Session: {$sessionName}\n" .
                           "🏷️ Batch: {$batchName}\n" .
                           "📅 Date: {$sessionDate}\n\n" .
                           "Please make sure to attend on time!\n\n" .
                           "Warm regards,\nE3 Admin Team";

                $sent = $this->sendWhatsApp($mobile, $message);
                if ($sent) {
                    $totalSent++;
                    Log::info("Automated session reminder sent to {$participant->first_name} ({$mobile}) for session '{$sessionName}'");
                }
            }
        }

        $this->info("Automated session reminders process completed. Total sent: {$totalSent}");
        return 0;
    }

    private function sendWhatsApp($mobile, $message)
    {
        try {
            $apiUrl   = env('WHATSAPP_API_URL', 'http://api.dotphi.com/wapp/v2/api/send');
            $apiToken = env('WHATSAPP_API_TOKEN', '');

            if (empty($apiToken)) {
                return false;
            }

            $response = Http::get($apiUrl, [
                'apikey' => $apiToken,
                'mobile' => $mobile,
                'msg'    => $message,
            ]);

            if ($response->successful()) {
                $body = trim($response->body());
                $result = $response->json();

                if (is_array($result)) {
                    $status = $result['status'] ?? $result['code'] ?? null;
                    $msg = $result['message'] ?? $result['msg'] ?? '';
                    
                    if ($status == 200 || $status === true || $status === 'success' || 
                        stripos((string)$status, 'success') !== false || stripos((string)$msg, 'success') !== false) {
                        return true;
                    }
                } else {
                    if ($body === "200" || stripos($body, "success") !== false) {
                        return true;
                    }
                }
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Exception in SendSessionReminders command: " . $e->getMessage());
            return false;
        }
    }
}
