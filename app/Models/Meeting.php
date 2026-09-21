<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormatDates;

class Meeting extends Model
{
    use HasFactory, FormatDates;

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function batchGroup()
    {
        return $this->belongsTo(BatchGroup::class);
    }

    public function batchSchedule()
    {
        return $this->belongsTo(BatchSchedule::class);
    }

    public function coach()
    {
        return $this->belongsTo(Participant::class);
    }
    public function attendances()
    {
        return $this->hasMany(MeetingAttendance::class);
    }
    public function getDateAttribute($value)
    {
        return $this->formatDate($value);
    }

    public function getTimeAttribute($value)
    {
        return $this->formatTime($value);
    }
}
