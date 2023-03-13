<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderData extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public $timestamps = false;

    public function parent()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    
}