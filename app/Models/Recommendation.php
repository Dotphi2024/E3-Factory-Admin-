<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    public function participant(){
        return $this->belongsTo(Participant::class);
    }
    public function followUps(){
        return $this->hasMany(FollowUp::class)->with('addedBy');
    }
}
