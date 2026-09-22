<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ValuePost;
use App\Models\Participant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Auth;

class ValuePostController extends Controller
{
    public function list()
    {
        $posts = ValuePost::orderBy('id', 'desc')->get();

        $events = $posts->map(function($post) {
            $eventDate = $post->scheduled_at ? $post->scheduled_at : $post->created_at;
            $isScheduled = ($post->status === 'scheduled');
            
            return [
                'id' => $post->id,
                'title' => ($isScheduled ? '⏰ [Scheduled] ' : '✅ [Sent] ') . $post->title,
                'start' => $eventDate->toIso8601String(),
                'backgroundColor' => $isScheduled ? '#0d6efd' : '#198754',
                'borderColor' => $isScheduled ? '#0b5ed7' : '#146c43',
                'extendedProps' => [
                    'post_id' => $post->id,
                    'title' => $post->title,
                    'message' => $post->message,
                    'image' => $post->image ? asset('uploads/value-posts/' . $post->image) : null,
                    'status' => $post->status,
                    'sent_count' => $post->sent_count,
                    'scheduled_at' => $post->scheduled_at ? $post->scheduled_at->format('d-m-Y') : 'N/A',
                    'created_at' => $post->created_at->format('d-m-Y H:i'),
                    'created_by' => $post->addedBy ? $post->addedBy->name : 'Admin',
                    'resend_url' => route('master.value-posts.resend', $post->id),
                    'send_now_url' => route('master.value-posts.send-now', $post->id),
                    'delete_url' => route('master.value-posts.delete', $post->id),
                ]
            ];
        });

        return view('master.value-posts.list', compact('posts', 'events'));
    }

    public function add(Request $request)
    {
        $activeParticipantCount = Participant::where('is_active', 1)->whereNotNull('mobile')->where('mobile', '!=', '')->count();
        $presetDate = $request->query('date', '');
        if ($presetDate) {
            $presetDate = date('Y-m-d', strtotime($presetDate));
        }
        return view('master.value-posts.create', compact('activeParticipantCount', 'presetDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'post_type' => 'nullable|string|in:now,schedule',
            'scheduled_at' => 'nullable|required_if:post_type,schedule|date',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/value-posts'), $imageName);
        }

        $post = new ValuePost();
        $post->title = $request->title;
        $post->message = $request->message;
        $post->image = $imageName;
        $post->added_by = Auth::id();

        if ($request->post_type === 'schedule' && !empty($request->scheduled_at)) {
            $post->scheduled_at = date('Y-m-d 00:00:00', strtotime($request->scheduled_at));
            $post->status = 'scheduled';
            $post->sent_count = 0;
            $post->save();

            return redirect()->route('master.value-posts.list')->with('success', "Value Post scheduled successfully for " . date('d-m-Y', strtotime($request->scheduled_at)) . "!");
        } else {
            @set_time_limit(300);
            $post->status = 'sent';
            $post->scheduled_at = now();
            $post->save();

            // Broadcast to all active participants via WhatsApp
            $sentCount = $this->broadcastWhatsAppMessage($request->message, $imageName);

            $post->sent_count = $sentCount;
            $post->save();

            return redirect()->route('master.value-posts.list')->with('success', "Value Post created and sent to {$sentCount} active participants via WhatsApp!");
        }
    }

    public function sendNow($id)
    {
        @set_time_limit(300);
        $post = ValuePost::findOrFail($id);
        $sentCount = $this->broadcastWhatsAppMessage($post->message, $post->image);

        $post->sent_count = $sentCount;
        $post->status = 'sent';
        $post->scheduled_at = now();
        $post->save();

        return redirect()->route('master.value-posts.list')->with('success', "Scheduled Value Post sent to {$sentCount} active participants via WhatsApp!");
    }

    public function resend($id)
    {
        @set_time_limit(300);
        $post = ValuePost::findOrFail($id);
        $sentCount = $this->broadcastWhatsAppMessage($post->message, $post->image);

        $post->sent_count = $sentCount;
        $post->status = 'sent';
        $post->save();

        return redirect()->back()->with('success', "Value Post resent to {$sentCount} active participants via WhatsApp!");
    }

    public function delete($id)
    {
        $post = ValuePost::findOrFail($id);
        if ($post->image && file_exists(public_path('uploads/value-posts/' . $post->image))) {
            @unlink(public_path('uploads/value-posts/' . $post->image));
        }
        $post->delete();

        return redirect()->route('master.value-posts.list')->with('success', 'Value Post deleted successfully!');
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
                Log::error("Failed to send WhatsApp Value Post to {$mobile}: " . $e->getMessage());
            }
        }

        return $successCount;
    }
}
