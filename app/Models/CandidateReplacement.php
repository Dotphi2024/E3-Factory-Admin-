<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateReplacement extends Model
{
    use HasFactory;

    protected $table = 'candidate_replacements';

    protected $fillable = [
        'batch_id',
        'original_participant_id',
        'candidate_first_name',
        'candidate_last_name',
        'candidate_mobile',
        'candidate_email',
        'candidate_data',
        'replacement_participant_id',
        'original_participant_batch_id',
        'new_participant_batch_id',
        'total_amount_transferred',
        'registration_fee_transferred',
        'session_fees_transferred',
        'paid_sessions_transferred',
        'transferred_payment_ids',
        'reason',
        'policy_accepted',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'candidate_data' => 'array',
        'paid_sessions_transferred' => 'array',
        'transferred_payment_ids' => 'array',
        'policy_accepted' => 'boolean',
        'approved_at' => 'datetime',
        'total_amount_transferred' => 'decimal:2',
        'registration_fee_transferred' => 'decimal:2',
        'session_fees_transferred' => 'decimal:2',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function originalParticipant()
    {
        return $this->belongsTo(Participant::class, 'original_participant_id');
    }

    public function replacementParticipant()
    {
        return $this->belongsTo(Participant::class, 'replacement_participant_id');
    }

    public function originalEnrollment()
    {
        return $this->belongsTo(ParticipantBatch::class, 'original_participant_batch_id');
    }

    public function newEnrollment()
    {
        return $this->belongsTo(ParticipantBatch::class, 'new_participant_batch_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
