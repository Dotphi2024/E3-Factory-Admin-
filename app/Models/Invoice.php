<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function center()
    {
        return $this->belongsTo(Center::class);
    }
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}