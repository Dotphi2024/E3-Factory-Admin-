<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Participant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendFeeDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'participants:send-fee-due-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated WhatsApp pending fee payment reminders to active participants with due amounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $participants = Participant::active()
            ->where('due_amount', '>', 0)
            ->get();

        if ($participants->isEmpty()) {
            $this->info('No participants with pending fee due amounts found.');
            return 0;
        }

        $this->info('Found ' . $participants->count() . ' participant(s) with pending fees.');
        $totalSent = 0;

        foreach ($participants as $participant) {
            $mobile = preg_replace('/[^0-9]/', '', $participant->mobile);
            if (empty($mobile)) continue;

            if (strlen($mobile) === 10) {
                $mobile = '91' . $mobile;
            }

            $dueAmountFormatted = number_format($participant->due_amount);

            $message = "Dear {$participant->first_name} {$participant->last_name},\n\n" .
                       "This is an automated friendly reminder regarding your pending fees with E3.\n\n" .
                       "💰 Pending Due Amount: ₹{$dueAmountFormatted}\n\n" .
                       "Kindly clear your due payment at your earliest convenience.\n\n" .
                       "Thank you!\nE3 Admin Team";

            $sent = $this->sendWhatsApp($mobile, $message);

            if ($sent) {
                $totalSent++;
                $this->info("Fee due reminder sent to {$participant->first_name} ({$mobile}).");
                Log::info("Automated Fee Due WhatsApp reminder sent to {$participant->first_name} ({$mobile})");
            } else {
                $this->warn("Failed to send fee due reminder to {$participant->first_name} ({$mobile}).");
            }
        }

        $this->info("Automated fee due reminder process completed. Total sent: {$totalSent}");
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
            Log::error("Exception in SendFeeDueReminders command: " . $e->getMessage());
            return false;
        }
    }
}
