<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantSessionRating extends Model
{
    use HasFactory;
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function batchSchedule()
    {
        return $this->belongsTo(BatchSchedule::class);
    }
    public function batch(){
        return $this->belongsTo(Batch::class);
    }
    public function coach(){
        return $this->belongsTo(Participant::class);
    }
}
