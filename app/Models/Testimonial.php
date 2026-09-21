<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
