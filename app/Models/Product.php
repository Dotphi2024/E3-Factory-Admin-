<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
    public function tax(){
        return $this->belongsTo(Tax::class);
    }
}
