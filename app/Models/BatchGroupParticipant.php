<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchGroupParticipant extends Model
{
    use HasFactory;

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function batch_group()
    {
        return $this->belongsTo(BatchGroup::class);
    }
}
