<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchBanner extends Model
{
    use HasFactory;
    protected $fillable=[
        'batch_id',
        'banner',
        'terms_and_conditions',
        'is_active',
        'is_i_agree_mark'
    ];
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
