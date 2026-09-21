<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchGroup extends Model
{
    use HasFactory;
    public function coach()
    {
        return $this->belongsTo(Participant::class, 'coach_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function batchGroupParticipants()
    {
        return $this->hasMany(BatchGroupParticipant::class)->with('participant');
    }

    public function batchGroupParticipants1(){
        return $this->belongsToMany(Participant::class, 'batch_group_participants', 'batch_group_id', 'participant_id');
    }
    public function messages()
    {
        return $this->hasMany(GroupMessage::class);
    }
}
