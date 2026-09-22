<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ValuePost;
use App\Models\Participant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendScheduledValuePosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'value-posts:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send due scheduled WhatsApp Value Posts to all active participants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $duePosts = ValuePost::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($duePosts->isEmpty()) {
            $this->info('No due scheduled Value Posts found.');
            return 0;
        }

        foreach ($duePosts as $post) {
            $this->info("Sending scheduled post ID #{$post->id}: {$post->title}");
            $sentCount = $this->broadcastWhatsAppMessage($post->message, $post->image);
            $post->sent_count = $sentCount;
            $post->status = 'sent';
            $post->save();

            $this->info("Post ID #{$post->id} sent to {$sentCount} active participants.");
        }

        return 0;
    }

    private function broadcastWhatsAppMessage($message, $imageName = null)
    {
        $participants = Participant::where('is_active', 1)->whereNotNull('mobile')->where('mobile', '!=', '')->get();
        $apiUrl   = env('WHATSAPP_API_URL', 'https://api.dotphi.com/wapp/v2/api/send');
        $apiToken = env('WHATSAPP_API_TOKEN', '');
        $successCount = 0;

        $imageUrl = $imageName ? asset('uploads/value-posts/' . $imageName) : null;

        foreach ($participants as $participant) {
            $mobile = preg_replace('/[^0-9]/', '', $participant->mobile);
            if (empty($mobile)) {
                continue;
            }
            if (strlen($mobile) === 10) {
                $mobile = '91' . $mobile;
            }

            try {
                $params = [
                    'apikey' => $apiToken,
                    'mobile' => $mobile,
                    'msg'    => $message,
                ];

                if ($imageUrl) {
                    $params['img_url']   = $imageUrl;
                    $params['media_url'] = $imageUrl;
                    $params['img']       = $imageUrl;
                }

                $response = Http::timeout(5)->get($apiUrl, $params);

                if ($response->successful()) {
                    $successCount++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send scheduled WhatsApp Value Post to {$mobile}: " . $e->getMessage());
            }
        }

        return $successCount;
    }
}
