<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValuePost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'image',
        'sent_count',
        'scheduled_at',
        'status',
        'added_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
