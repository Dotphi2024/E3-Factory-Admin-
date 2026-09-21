<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupMessage extends Model
{
    use HasFactory, SoftDeletes;

    public function batchGroup()
    {
        return $this->belongsTo(BatchGroup::class);
    }

    public function batch(){
        return $this->belongsTo(Batch::class);
    }

    public function coach(){
        return $this->belongsTo(Participant::class);
    }
    public function participant(){
        return $this->belongsTo(Participant::class);
    }
}
