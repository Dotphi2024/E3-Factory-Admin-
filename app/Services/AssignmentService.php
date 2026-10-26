<?php

namespace App\Services;

use App\Models\BatchSchedule;
use App\Models\Assignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AssignmentService
{
    /**
     * Check if a batch schedule should have its assignments auto-released.
     * Rule: On the day of the session at 6:00 PM (18:00) or any time after.
     */
    public static function shouldAutoRelease(BatchSchedule $schedule): bool
    {
        $rawDate = $schedule->getRawOriginal('date') ?? $schedule->date;
        if (empty($rawDate)) {
            return false;
        }

        try {
            $sessionDate = Carbon::parse($rawDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return false;
        }

        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentHour = (int) $now->format('H'); // 0 - 23

        // Past session date
        if ($sessionDate < $today) {
            return true;
        }

        // Today's session date and current time is 6:00 PM (18:00) or later
        if ($sessionDate === $today && $currentHour >= 18) {
            return true;
        }

        return false;
    }

    /**
     * Get human-readable auto-release status for a batch schedule.
     */
    public static function getAutoReleaseStatus(BatchSchedule $schedule): array
    {
        $rawDate = $schedule->getRawOriginal('date') ?? $schedule->date;
        if (empty($rawDate)) {
            return [
                'status' => 'no_date',
                'badge_class' => 'bg-secondary',
                'label' => 'No Session Date Set',
                'is_due' => false,
                'session_date_formatted' => 'N/A',
            ];
        }

        try {
            $sessionDate = Carbon::parse($rawDate)->format('Y-m-d');
            $displayDate = Carbon::parse($rawDate)->format('d-m-Y');
        } catch (\Exception $e) {
            return [
                'status' => 'invalid_date',
                'badge_class' => 'bg-secondary',
                'label' => 'Invalid Date',
                'is_due' => false,
                'session_date_formatted' => $rawDate,
            ];
        }

        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentHour = (int) $now->format('H');

        if ($sessionDate > $today) {
            return [
                'status' => 'scheduled_future',
                'badge_class' => 'bg-info text-dark',
                'label' => "Auto-release scheduled on {$displayDate} at 6:00 PM",
                'is_due' => false,
                'session_date_formatted' => $displayDate,
            ];
        }

        if ($sessionDate === $today) {
            if ($currentHour < 18) {
                return [
                    'status' => 'scheduled_today',
                    'badge_class' => 'bg-warning text-dark',
                    'label' => 'Auto-releasing Today at 6:00 PM',
                    'is_due' => false,
                    'session_date_formatted' => $displayDate,
                ];
            } else {
                return [
                    'status' => 'released_today',
                    'badge_class' => 'bg-success',
                    'label' => 'Auto-released Today at 6:00 PM',
                    'is_due' => true,
                    'session_date_formatted' => $displayDate,
                ];
            }
        }

        return [
            'status' => 'released_past',
            'badge_class' => 'bg-success',
            'label' => "Auto-released on {$displayDate} at 6:00 PM",
            'is_due' => true,
            'session_date_formatted' => $displayDate,
        ];
    }

    /**
     * Auto-release unstarted assignments for a specific schedule if due.
     */
    public static function autoReleaseForSchedule(BatchSchedule $schedule, ?FcmNotificationService $fcm = null): int
    {
        if (!self::shouldAutoRelease($schedule)) {
            return 0;
        }

        $unstarted = $schedule->assignments()->where('is_started', 0)->get();
        if ($unstarted->isEmpty()) {
            return 0;
        }

        $count = 0;
        foreach ($unstarted as $assignment) {
            $assignment->is_started = 1;
            $assignment->save();
            $count++;
        }

        if ($count > 0 && $fcm) {
            $batch = $schedule->batch;
            if ($batch) {
                $device_tokens = $batch->batchParticipants()
                    ->where('participants.is_active', 1)
                    ->whereNotNull('participants.device_token')
                    ->pluck('participants.device_token')
                    ->toArray();

                if (!empty($device_tokens)) {
                    $fcm->sendNotification(
                        $device_tokens,
                        'Session Assignment Released',
                        "Assignments for session '{$schedule->name}' are now open.",
                        'session_assignment_released',
                        $schedule
                    );
                }
            }
        }

        return $count;
    }

    /**
     * Auto-release all due assignments across all schedules.
     */
    public static function autoReleaseAllDue(?FcmNotificationService $fcm = null): int
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentHour = (int) $now->format('H');

        $schedules = BatchSchedule::with(['batch.batchParticipants', 'assignments'])
            ->whereHas('assignments', function ($q) {
                $q->where('is_started', 0);
            })
            ->get();

        $totalReleased = 0;

        foreach ($schedules as $schedule) {
            $rawDate = $schedule->getRawOriginal('date') ?? $schedule->date;
            if (empty($rawDate)) continue;

            try {
                $sessionDate = Carbon::parse($rawDate)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }

            if ($sessionDate > $today) continue;
            if ($sessionDate === $today && $currentHour < 18) continue;

            $released = self::autoReleaseForSchedule($schedule, $fcm);
            $totalReleased += $released;
        }

        return $totalReleased;
    }
}
