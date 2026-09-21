<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormatDates;

class Batch extends Model
{
    use HasFactory, FormatDates;

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function batchParticipants(){
        return $this->belongsToMany(Participant::class, 'participant_batches', 'batch_id', 'participant_id')->withPivot('id','is_registration_fees_paid');
    }

    public function payments(){
        return $this->hasMany(ParticipantPayment::class);
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
    public function banners(){
        return $this->hasMany(BatchBanner::class);
    }
    public function coaches(){
        return $this->belongsToMany(Participant::class, 'coaches', 'batch_id', 'participant_id')->withPivot('is_head_coach','added_by','id');
    }
    public function groups(){
        return $this->hasMany(BatchGroup::class);
    }
    public function schedules(){
        return $this->hasMany(BatchSchedule::class);
    }

    public function getStartDateAttribute($value){
        return $this->formatDate($value);
    }
    public function getEndDateAttribute($value){
        return $this->formatDate($value);
    }
}
