<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormatDates;

class FollowUp extends Model
{
    use HasFactory,FormatDates;
    public function addedBy(){
        return $this->belongsTo(User::class,'added_by');
    }

    public function recommendation(){
        return $this->belongsTo(Recommendation::class);
    }
    public function getDateAttribute($value)
    {
        return $this->formatDate($value);
    }
    public function getTimeAtrribute($value)
    {
        return $this->formatTime($value);
    }
}
