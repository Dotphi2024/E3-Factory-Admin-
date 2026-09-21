<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function assignmentOption()
    {
        return $this->belongsTo(AssignmentOption::class);
    }
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    public function batchSchedule()
    {
        return $this->belongsTo(BatchSchedule::class);
    }
}
