<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Participant;
use App\Models\Course;
use App\Models\User;

class DashboardController extends Controller
{
    public function dashboard(){
        $batches = Batch::orderBy('id', 'desc')->get();
        $participants = Participant::all();
        $courses = Course::all();
        $users = User::all();

        // Dynamically gather all reference types (defaults + any saved in database)
        $defaultRefTypes = ['Member', 'Coach', 'Head Coach', 'Offline Orientation', 'Online Orientation', 'Social Media', 'Advertisement'];
        $dbRefs = Participant::whereNotNull('reference')->where('reference', '!=', '')->pluck('reference')->unique()->toArray();
        $refTypes = array_values(array_unique(array_merge($defaultRefTypes, $dbRefs)));

        // Precalculate stats per batch and overall
        $batchStats = [];

        $allTotal = (float) $participants->sum('total_amount');
        $allPaid  = (float) $participants->sum('paid_amount');
        $allDue   = $allTotal - $allPaid;

        $allRefCounts = [];
        foreach ($refTypes as $type) {
            $allRefCounts[$type] = $participants->where('reference', $type)->count();
        }

        $allPendingParticipants = $participants->filter(function($p) {
            return (float)$p->due_amount > 0;
        })->map(function($p) {
            $refText = $p->reference ?: 'N/A';
            if (in_array($p->reference, ['Member', 'Coach', 'Head Coach']) && $p->referenceBy) {
                $refText .= ' (' . $p->referenceBy->first_name . ' ' . $p->referenceBy->last_name . ')';
            } elseif ($p->reference_detail) {
                $refText .= ' (' . $p->reference_detail . ')';
            }
            return [
                'name'         => $p->first_name . ' ' . $p->last_name,
                'mobile'       => $p->mobile,
                'batch_name'   => $p->batch ? $p->batch->name : 'N/A',
                'reference'    => $refText,
                'total_amount' => (float)$p->total_amount,
                'paid_amount'  => (float)$p->paid_amount,
                'due_amount'   => (float)$p->due_amount,
                'city'         => $p->city ?: 'N/A',
            ];
        })->values()->toArray();

        $batchStats['all'] = [
            'participants_count'   => $participants->count(),
            'total_amount'         => $allTotal,
            'paid_amount'          => $allPaid,
            'due_amount'           => $allDue,
            'references'           => $allRefCounts,
            'pending_participants' => $allPendingParticipants,
        ];

        foreach ($batches as $batch) {
            $batchParticipants = Participant::where('batch_id', $batch->id)
                ->orWhereHas('participantBatches', function($q) use ($batch) {
                    $q->where('batch_id', $batch->id);
                })->distinct()->get();

            $totalAmount = (float) $batchParticipants->sum('total_amount');
            $paidAmount  = (float) $batchParticipants->sum('paid_amount');

            // If participant total_amount is 0 for this batch, check batch fee structure
            if ($totalAmount <= 0) {
                $batchFee = (float)$batch->registration_fee + ((float)$batch->number_of_sessions * (float)$batch->fee_per_session);
                $totalAmount = $batchFee * $batchParticipants->count();
            }

            $dueAmount = $totalAmount - $paidAmount;

            $batchRefCounts = [];
            foreach ($refTypes as $type) {
                $batchRefCounts[$type] = $batchParticipants->where('reference', $type)->count();
            }

            $batchPendingParticipants = $batchParticipants->filter(function($p) {
                return (float)$p->due_amount > 0;
            })->map(function($p) use ($batch) {
                $refText = $p->reference ?: 'N/A';
                if (in_array($p->reference, ['Member', 'Coach', 'Head Coach']) && $p->referenceBy) {
                    $refText .= ' (' . $p->referenceBy->first_name . ' ' . $p->referenceBy->last_name . ')';
                } elseif ($p->reference_detail) {
                    $refText .= ' (' . $p->reference_detail . ')';
                }
                return [
                    'name'         => $p->first_name . ' ' . $p->last_name,
                    'mobile'       => $p->mobile,
                    'batch_name'   => $batch->name,
                    'reference'    => $refText,
                    'total_amount' => (float)$p->total_amount,
                    'paid_amount'  => (float)$p->paid_amount,
                    'due_amount'   => (float)$p->due_amount,
                    'city'         => $p->city ?: 'N/A',
                ];
            })->values()->toArray();

            $batchStats[$batch->id] = [
                'participants_count'   => $batchParticipants->count(),
                'total_amount'         => $totalAmount,
                'paid_amount'          => $paidAmount,
                'due_amount'           => $dueAmount,
                'references'           => $batchRefCounts,
                'pending_participants' => $batchPendingParticipants,
            ];
        }

        return view('master.dashboard', compact('batches', 'participants', 'courses', 'users', 'batchStats', 'refTypes'));
    }
}
