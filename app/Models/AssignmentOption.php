<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentOption extends Model
{
    use HasFactory;
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
    public function batch_schedule()
    {
        return $this->belongsTo(BatchSchedule::class);
    }
    public function submission()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
