<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormatDates;

class BatchSchedule extends Model
{
    use HasFactory, FormatDates;
    protected $fillable=['batch_id','name','amount','date','payment_link_status','session_number','is_session_completed'];
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class)->with('assignment','assignmentOption');
    }
    public function sessionRatings()
    {
        return $this->hasMany(ParticipantSessionRating::class);
    }
    public function getDateAtrribute($value){
        return $this->formatDate($value);
    }
}
