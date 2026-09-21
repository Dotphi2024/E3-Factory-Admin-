<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Participant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendBirthdayGreetings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'participants:send-birthday-wishes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated birthday wishes via WhatsApp to participants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $todayMonth = date('m');
        $todayDay = date('d');

        $participants = Participant::active()
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $todayMonth)
            ->whereDay('birth_date', $todayDay)
            ->get();

        if ($participants->isEmpty()) {
            $this->info('No birthdays today.');
            return 0;
        }

        $this->info('Found ' . $participants->count() . ' participant(s) with birthday today.');

        foreach ($participants as $participant) {
            $name = $participant->first_name . ' ' . $participant->last_name;
            $mobile = preg_replace('/[^0-9]/', '', $participant->mobile);

            if (empty($mobile)) {
                $this->warn("Skipping $name: No mobile number.");
                continue;
            }

            if (strlen($mobile) === 10) {
                $mobile = '91' . $mobile;
            }

            $message = "Dear " . $participant->first_name . " " . $participant->last_name . ",\n\n" .
                       "Wishing you a very Happy Birthday! 🎂✨ May this year bring you joy, good health, and success.\n\n" .
                       "Best regards,\nE3 Admin Team";

            $sent = $this->sendWhatsApp($mobile, $message);

            if ($sent) {
                $this->info("Birthday wish sent successfully to $name ($mobile).");
                Log::info("Birthday WhatsApp wish sent to $name ($mobile)");
            } else {
                $this->error("Failed to send birthday wish to $name ($mobile).");
                Log::error("Failed to send birthday WhatsApp wish to $name ($mobile)");
            }
        }

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
                    
                    if ($status == 200 || 
                        $status === true || 
                        $status === 'success' || 
                        (is_string($status) && stripos($status, 'success') !== false) ||
                        stripos($msg, 'success') !== false ||
                        stripos($msg, 'submitted') !== false ||
                        stripos($msg, 'sent') !== false) {
                        return true;
                    }
                } else {
                    if ($body === "200" || 
                        stripos($body, "success") !== false || 
                        stripos($body, "submitted") !== false ||
                        stripos($body, "sent") !== false) {
                        return true;
                    }
                }
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Exception in SendBirthdayGreetings command: " . $e->getMessage());
            return false;
        }
    }
}
