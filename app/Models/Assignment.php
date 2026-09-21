<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    public function batchSchedule(){
        return $this->belongsTo(BatchSchedule::class);
    }

    public function options(){
        return $this->hasMany(AssignmentOption::class);
    }
    public function submissions(){
        return $this->hasMany(AssignmentSubmission::class);
    }
    // public function createdBy(){
    //     return $this->belongsTo(User::class,'created_by');
    // }
}
