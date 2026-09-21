<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function referenceBy()
    {
        return $this->belongsTo(Participant::class, 'reference_detail');
    }

    public function payments()
    {
        return $this->hasMany(ParticipantPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'coaches', 'participant_id', 'batch_id')->withPivot('is_head_coach','added_by','id');
    }
    public function batchGroupParticipants()
    {
        return $this->hasMany(BatchGroupParticipant::class);
    }

    public function assignedGroups(){
        return $this->hasMany(BatchGroup::class, 'coach_id', 'id');
    }

    public function batchGroups()
    {
        return $this->belongsToMany(BatchGroup::class, 'batch_group_participants', 'participant_id', 'batch_group_id');
    }

    public function participantBatches(){
        return $this->belongsToMany(Batch::class, 'participant_batches', 'participant_id', 'batch_id')->withPivot('id','is_registration_fees_paid');
    }
    public function meetingAttendances(){
        return $this->hasMany(MeetingAttendance::class);
    }

    public function assignmentSubmissions(){
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function sessionRatings()
    {
        return $this->hasMany(ParticipantSessionRating::class);
    }

    public function getAddedByUserAttribute()
    {
        if ($this->pivot && $this->pivot->added_by) {
            return User::find($this->pivot->added_by);
        }
        return null;
    }
}
