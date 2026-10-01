<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantBatch extends Model
{
    use HasFactory;

    protected $casts = [
        'is_registration_fees_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function replacedByParticipant()
    {
        return $this->belongsTo(Participant::class, 'replaced_by_participant_id');
    }

    public function replacement()
    {
        return $this->belongsTo(CandidateReplacement::class, 'replacement_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->where('enrollment_status', 'active');
    }
}
